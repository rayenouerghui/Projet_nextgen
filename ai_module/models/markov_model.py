"""
Markov Model - Détecte les phrases non naturelles
"""
import json
import os
import re
from collections import defaultdict
import math

class MarkovModel:
    def __init__(self, data_path):
        self.data_path = data_path
        self.samples = {}
        self.word_transitions = defaultdict(lambda: defaultdict(int))
        self.char_transitions = defaultdict(lambda: defaultdict(int))
        self.word_starts = defaultdict(int)
        self.char_starts = defaultdict(int)
        self.vocab_size = 0
        self.load_data()
        self.train()
    
    def load_data(self):
        """Charger les données d'entraînement"""
        samples_file = os.path.join(self.data_path, 'reclamations_samples.json')
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
        """Entraîner le modèle de Markov sur les données valides"""
        valid_sentences = self.samples.get('reclamations_valides', [])
        
        # Entraîner sur les transitions de mots
        for sentence in valid_sentences:
            tokens = self.tokenize(sentence)
            if tokens:
                # Premier mot
                self.word_starts[tokens[0]] += 1
                
                # Transitions de mots
                for i in range(len(tokens) - 1):
                    self.word_transitions[tokens[i]][tokens[i + 1]] += 1
        
        # Entraîner sur les transitions de caractères
        for sentence in valid_sentences:
            normalized = self.normalize_text(sentence)
            if normalized:
                # Premier caractère
                self.char_starts[normalized[0]] += 1
                
                # Transitions de caractères
                for i in range(len(normalized) - 1):
                    self.char_transitions[normalized[i]][normalized[i + 1]] += 1
        
        # Calculer la taille du vocabulaire
        self.vocab_size = len(self.word_transitions) + 1
    
    def calculate_word_probability(self, tokens):
        """Calculer la probabilité d'une séquence de mots"""
        if not tokens:
            return 0.0
        
        log_prob = 0.0
        total_starts = sum(self.word_starts.values())
        
        # Probabilité du premier mot
        if total_starts > 0:
            first_word_prob = (self.word_starts.get(tokens[0], 0.1) + 1) / (total_starts + self.vocab_size)
            log_prob += math.log(first_word_prob)
        
        # Probabilités des transitions
        for i in range(len(tokens) - 1):
            current_word = tokens[i]
            next_word = tokens[i + 1]
            
            total_transitions = sum(self.word_transitions[current_word].values())
            if total_transitions > 0:
                transition_prob = (self.word_transitions[current_word].get(next_word, 0.1) + 1) / (total_transitions + self.vocab_size)
            else:
                transition_prob = 1.0 / (self.vocab_size + 1)
            
            log_prob += math.log(transition_prob)
        
        return log_prob
    
    def calculate_char_probability(self, text):
        """Calculer la probabilité d'une séquence de caractères"""
        normalized = self.normalize_text(text)
        if not normalized or len(normalized) < 2:
            return 0.5  # Texte trop court
        
        log_prob = 0.0
        total_starts = sum(self.char_starts.values())
        
        # Probabilité du premier caractère
        if total_starts > 0:
            first_char_prob = (self.char_starts.get(normalized[0], 0.1) + 1) / (total_starts + 26)
            log_prob += math.log(first_char_prob)
        
        # Probabilités des transitions
        for i in range(len(normalized) - 1):
            current_char = normalized[i]
            next_char = normalized[i + 1]
            
            total_transitions = sum(self.char_transitions[current_char].values())
            if total_transitions > 0:
                transition_prob = (self.char_transitions[current_char].get(next_char, 0.1) + 1) / (total_transitions + 26)
            else:
                transition_prob = 1.0 / 27
            
            log_prob += math.log(transition_prob)
        
        return log_prob
    
    def detect_nonsense(self, text):
        """Détecte si le texte est non-naturel"""
        # Détections simples de patterns non naturels
        normalized = self.normalize_text(text)
        tokens = self.tokenize(text)
        
        # Vérifier les répétitions de caractères
        if re.search(r'([a-z])\1{3,}', normalized):
            return True, 0.95, "Répétition excessive de caractères détectée"
        
        # Vérifier les répétitions de mots
        if len(tokens) > 2:
            repeated_words = sum(1 for i in range(len(tokens) - 1) if tokens[i] == tokens[i + 1])
            if repeated_words > len(tokens) * 0.3:
                return True, 0.90, "Répétition excessive de mots détectée"
        
        # Vérifier si c'est du charabia (peu de transitions valides)
        word_prob = self.calculate_word_probability(tokens)
        char_prob = self.calculate_char_probability(text)
        
        # Seuils pour détecter du non-sens
        if word_prob < -15 and char_prob < -5:
            return True, 0.85, "Séquence de mots peu naturelle"
        
        return False, 0.0, "Phrase naturelle"
    
    def get_naturalness_score(self, text):
        """Obtenir un score de naturalité entre 0 et 1"""
        tokens = self.tokenize(text)
        
        if not tokens:
            return 0.0
        
        word_prob = self.calculate_word_probability(tokens)
        char_prob = self.calculate_char_probability(text)
        
        # Normaliser les probabilités logarithmiques
        # Les valeurs typiques sont négatives, on les ramène à 0-1
        combined_prob = (word_prob + char_prob) / 2
        
        # Conversion logarithmique à probabilité
        naturalness = 1.0 / (1.0 + math.exp(-combined_prob / 10))
        
        return min(max(naturalness, 0.0), 1.0)
