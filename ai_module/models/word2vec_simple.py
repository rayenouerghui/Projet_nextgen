"""
Word2Vec Simplifié - Analyse sémantique et cohérence contextuelle
"""
import json
import os
import re
import math
from collections import defaultdict

class SimpleWord2Vec:
    def __init__(self, data_path):
        self.data_path = data_path
        self.word_vectors = {}
        self.context_keywords = {}
        self.insult_keywords = {}
        self.load_embeddings()
    
    def load_embeddings(self):
        """Charger les word embeddings"""
        embeddings_file = os.path.join(self.data_path, 'word_embeddings.json')
        with open(embeddings_file, 'r', encoding='utf-8') as f:
            embeddings = json.load(f)
            self.context_keywords = embeddings.get('context_words', {})
            self.insult_keywords = embeddings.get('insult_words', {})
    
    def normalize_text(self, text):
        """Normaliser le texte"""
        text = text.lower()
        text = re.sub(r'[^a-zàâäéèêëîïôöùûüçœæ0-9\s]', '', text)
        return text.strip()
    
    def tokenize(self, text):
        """Diviser le texte en mots"""
        normalized = self.normalize_text(text)
        return normalized.split()
    
    def get_vector(self, word):
        """Obtenir le vecteur d'un mot"""
        # Si le mot existe dans nos embeddings
        if word in self.context_keywords:
            value = self.context_keywords[word]
            # Créer un vecteur simple basé sur la valeur
            return self._create_vector(word, value)
        elif word in self.insult_keywords:
            value = self.insult_keywords[word]
            return self._create_vector(word, value, is_insult=True)
        else:
            # Vecteur neutre pour les mots inconnus
            return self._create_vector(word, 0.5)
    
    def _create_vector(self, word, value, is_insult=False):
        """Créer un vecteur simple pour un mot"""
        # Vecteur en 3 dimensions: [contexte, polarité, longueur_word]
        context_score = value if not is_insult else 0.0
        insult_score = value if is_insult else 0.0
        length_score = min(len(word) / 15, 1.0)
        
        return {
            'context': context_score,
            'insult': insult_score,
            'length': length_score,
            'word': word
        }
    
    def cosine_similarity(self, vec1, vec2):
        """Calculer la similarité cosinus entre deux vecteurs"""
        dot_product = (vec1['context'] * vec2['context'] + 
                      vec1['insult'] * vec2['insult'] + 
                      vec1['length'] * vec2['length'])
        
        magnitude1 = math.sqrt(vec1['context']**2 + vec1['insult']**2 + vec1['length']**2)
        magnitude2 = math.sqrt(vec2['context']**2 + vec2['insult']**2 + vec2['length']**2)
        
        if magnitude1 == 0 or magnitude2 == 0:
            return 0.0
        
        return dot_product / (magnitude1 * magnitude2)
    
    def calculate_semantic_coherence(self, tokens):
        """Calculer la cohérence sémantique du texte"""
        if len(tokens) < 2:
            return 0.5
        
        # Vecteur "idéal" pour une réclamation
        ideal_vector = self._create_vector('ideal_complaint', 0.9)
        
        # Calculer la similarité moyenne avec l'idéal
        total_similarity = 0.0
        for token in tokens:
            vec = self.get_vector(token)
            similarity = self.cosine_similarity(vec, ideal_vector)
            total_similarity += similarity
        
        return total_similarity / len(tokens) if tokens else 0.5
    
    def detect_insult_semantics(self, tokens):
        """Détecter la présence sémantique d'insultes"""
        insult_score = 0.0
        insult_count = 0
        
        for token in tokens:
            vec = self.get_vector(token)
            if vec['insult'] > 0:
                insult_score += vec['insult']
                insult_count += 1
        
        if insult_count == 0:
            return 0.0
        
        return insult_score / insult_count
    
    def detect_context_relevance(self, tokens):
        """Détecter la pertinence contextuelle par rapport à une réclamation"""
        if not tokens:
            return 0.0
        
        total_relevance = 0.0
        for token in tokens:
            vec = self.get_vector(token)
            # Utiliser le score de contexte
            relevance = max(vec['context'], 0.0)
            total_relevance += relevance
        
        return total_relevance / len(tokens) if tokens else 0.0
    
    def analyze_text(self, text):
        """Analyse complète du texte"""
        tokens = self.tokenize(text)
        
        semantic_coherence = self.calculate_semantic_coherence(tokens)
        insult_level = self.detect_insult_semantics(tokens)
        context_relevance = self.detect_context_relevance(tokens)
        
        return {
            'semantic_coherence': semantic_coherence,
            'insult_level': insult_level,
            'context_relevance': context_relevance,
            'tokens': tokens
        }
