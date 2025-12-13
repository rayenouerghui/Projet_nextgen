"""
Naive Bayes Classifier - Classifie les messages en catégories
"""
import json
import os
import re
import math
from collections import defaultdict

class NaiveBayesClassifier:
    def __init__(self, data_path):
        self.data_path = data_path
        self.badwords = {}
        self.samples = {}
        self.class_probs = {}
        self.word_probs = defaultdict(lambda: defaultdict(float))
        self.load_data()
        self.train()
    
    def load_data(self):
        """Charger les données d'entraînement"""
        badwords_file = os.path.join(self.data_path, 'badwords_list.json')
        samples_file = os.path.join(self.data_path, 'reclamations_samples.json')
        
        with open(badwords_file, 'r', encoding='utf-8') as f:
            self.badwords = json.load(f)
        
        with open(samples_file, 'r', encoding='utf-8') as f:
            self.samples = json.load(f)
    
    def normalize_text(self, text):
        """Normaliser le texte"""
        text = text.lower()
        text = re.sub(r'[^a-zàâäéèêëîïôöùûüçœæ0-9\s]', '', text)
        return text.strip()
    
    def tokenize(self, text):
        """Diviser le texte en mots"""
        normalized = self.normalize_text(text)
        return normalized.split()
    
    def train(self):
        """Entraîner le modèle sur les données"""
        categories = {
            'valid': self.samples.get('reclamations_valides', []),
            'empty': self.samples.get('messages_vides_sans_sens', []),
            'repetitive': self.samples.get('phrases_repetitives', []),
            'short': self.samples.get('phrases_trop_courtes', [])
        }
        
        # Initialiser les probabilités des classes
        total_docs = sum(len(docs) for docs in categories.values())
        for cat, docs in categories.items():
            self.class_probs[cat] = len(docs) / total_docs if total_docs > 0 else 0.25
        
        # Calculer les probabilités des mots par classe
        for cat, docs in categories.items():
            word_counts = defaultdict(int)
            total_words = 0
            
            for doc in docs:
                tokens = self.tokenize(doc)
                for token in tokens:
                    word_counts[token] += 1
                    total_words += 1
            
            for word, count in word_counts.items():
                self.word_probs[cat][word] = count / total_words if total_words > 0 else 0.00001
    
    def classify(self, text):
        """Classifier un message"""
        tokens = self.tokenize(text)
        
        scores = {}
        for cat in self.class_probs.keys():
            # Score logarithmique pour éviter underflow
            score = math.log(self.class_probs[cat])
            
            for token in tokens:
                # Utiliser la probabilité du mot ou un lissage
                prob = self.word_probs[cat].get(token, 0.00001)
                score += math.log(prob)
            
            scores[cat] = score
        
        # Retourner la catégorie avec le score le plus élevé
        best_class = max(scores, key=lambda x: scores[x])
        
        # Normaliser les scores pour obtenir des probabilités
        max_score = max(scores.values()) if scores.values() else 0
        probabilities = {}
        sum_probs = 0
        
        for cat, score in scores.items():
            prob = math.exp(score - max_score)
            probabilities[cat] = prob
            sum_probs += prob
        
        for cat in probabilities:
            probabilities[cat] /= sum_probs
        
        return {
            'class': best_class,
            'confidence': probabilities[best_class],
            'probabilities': probabilities
        }
    
    def contains_badwords(self, text):
        """Vérifier si le texte contient des mots inappropriés"""
        normalized = self.normalize_text(text)
        tokens = self.tokenize(text)
        
        all_badwords = (
            self.badwords.get('insultes_francais', []) +
            self.badwords.get('insultes_dialecte_tunisien', []) +
            self.badwords.get('insultes_arabe', []) +
            self.badwords.get('spam_patterns', [])
        )
        
        for word in tokens:
            if word in all_badwords:
                return True
        
        return False
