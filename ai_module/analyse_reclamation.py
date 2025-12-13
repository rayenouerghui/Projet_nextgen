#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Système d'IA local pour filtrage de messages de réclamation
Fusionne 3 modèles: Naive Bayes, Markov, et Word2Vec simplifié
"""

import sys
import os
import json
import argparse

# Ajouter le répertoire des modèles au path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'models'))

from models.naive_bayes import NaiveBayesClassifier
from models.markov_model import MarkovModel
from models.word2vec_simple import SimpleWord2Vec

class ReclamationAnalyzer:
    def __init__(self):
        data_path = os.path.join(os.path.dirname(__file__), 'data')
        
        # Initialiser les modèles
        self.nb_classifier = NaiveBayesClassifier(data_path)
        self.markov_model = MarkovModel(data_path)
        self.word2vec = SimpleWord2Vec(data_path)
    
    def analyze(self, text):
        """Analyser un message de réclamation"""
        
        if not text or not isinstance(text, str):
            return {
                'valid': False,
                'reason': 'Message vide',
                'score': 0.0,
                'details': {}
            }
        
        text = text.strip()
        
        if len(text) < 5:
            return {
                'valid': False,
                'reason': 'Message trop court (minimum 5 caractères)',
                'score': 0.1,
                'details': {
                    'length': len(text)
                }
            }
        
        # === MODÈLE 1: Naive Bayes ===
        nb_result = self.nb_classifier.classify(text)
        nb_class = nb_result['class']
        nb_confidence = nb_result['confidence']
        
        # Déterminer le score Bayes
        if nb_class == 'valid':
            bayes_score = 0.8 * nb_confidence
        elif nb_class == 'empty' or nb_class == 'short':
            bayes_score = 0.0
        elif nb_class == 'repetitive':
            bayes_score = 0.2 * nb_confidence
        else:
            bayes_score = 0.5 * nb_confidence
        
        # Vérifier les mots inappropriés
        has_badwords = self.nb_classifier.contains_badwords(text)
        if has_badwords:
            bayes_score = 0.0
        
        # === MODÈLE 2: Markov ===
        is_nonsense, markov_nonsense_score, markov_reason = self.markov_model.detect_nonsense(text)
        naturalness_score = self.markov_model.get_naturalness_score(text)
        
        # Score Markov basé sur la naturalité
        markov_score = 0.7 * naturalness_score if not is_nonsense else 0.0
        
        # === MODÈLE 3: Word2Vec ===
        word2vec_result = self.word2vec.analyze_text(text)
        semantic_coherence = word2vec_result['semantic_coherence']
        insult_level = word2vec_result['insult_level']
        context_relevance = word2vec_result['context_relevance']
        
        # Score Word2Vec
        word2vec_score = (
            0.6 * semantic_coherence +
            0.2 * context_relevance -
            0.4 * insult_level
        )
        word2vec_score = max(min(word2vec_score, 1.0), 0.0)
        
        # === FUSION DES MODÈLES ===
        # Poids: Bayes 50%, Markov 30%, Word2Vec 20%
        final_score = (
            0.50 * bayes_score +
            0.30 * markov_score +
            0.20 * word2vec_score
        )
        
        # Déterminer la décision
        if has_badwords:
            decision = False
            reason = 'Message contenant des paroles impolis ou offensantes'
        elif final_score >= 0.70:
            decision = True
            reason = 'Message valide et approprié'
        elif final_score < 0.40:
            decision = False
            reason = self._determine_rejection_reason(
                nb_class, is_nonsense, markov_reason,
                insult_level, bayes_score, markov_score
            )
        else:
            decision = None  # Demander à l'utilisateur de réécrire
            reason = 'Message peu clair. Veuillez reformuler avec plus de détails.'
        
        return {
            'valid': decision,
            'reason': reason,
            'score': round(final_score, 3),
            'details': {
                'bayes_score': round(bayes_score, 3),
                'bayes_class': nb_class,
                'has_badwords': has_badwords,
                'markov_score': round(markov_score, 3),
                'markov_naturalness': round(naturalness_score, 3),
                'markov_nonsense': is_nonsense,
                'word2vec_score': round(word2vec_score, 3),
                'semantic_coherence': round(semantic_coherence, 3),
                'insult_level': round(insult_level, 3),
                'context_relevance': round(context_relevance, 3),
                'text_length': len(text),
                'word_count': len(word2vec_result['tokens'])
            }
        }
    
    def _determine_rejection_reason(self, nb_class, is_nonsense, markov_reason,
                                   insult_level, bayes_score, markov_score):
        """Déterminer la raison du rejet"""
        if insult_level > 0.5:
            return 'Message contenant des paroles offensantes ou insultes'
        
        if is_nonsense:
            return f'Message non compréhensible: {markov_reason}'
        
        if nb_class == 'empty' or bayes_score < 0.1:
            return 'Message vide ou sans contenu significatif'
        
        if nb_class == 'repetitive':
            return 'Message contenant trop de répétitions sans sens'
        
        if nb_class == 'short':
            return 'Message trop court pour être traité'
        
        if markov_score < 0.2:
            return 'Structure du message peu naturelle'
        
        return 'Message non approprié pour une réclamation'

def main():
    parser = argparse.ArgumentParser(description='Analyser un message de réclamation')
    parser.add_argument('message', nargs='?', help='Message à analyser')
    
    args = parser.parse_args()
    
    if not args.message:
        print(json.dumps({
            'valid': False,
            'reason': 'Aucun message fourni',
            'score': 0.0,
            'details': {}
        }))
        sys.exit(1)
    
    analyzer = ReclamationAnalyzer()
    result = analyzer.analyze(args.message)
    
    print(json.dumps(result, ensure_ascii=False))

if __name__ == '__main__':
    main()
