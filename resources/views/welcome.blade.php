@php
use Illuminate\Support\Facades\Route;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ELIGCRED - Évaluation de Crédit par IA</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            <style>
            .gradient {
                background: linear-gradient(90deg, #1a365d 0%, #2c5282 100%);
            }
            .feature-card {
                transition: transform 0.3s ease;
            }
            .feature-card:hover {
                transform: translateY(-5px);
            }
            </style>
    </head>
    <body class="antialiased">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-2xl font-bold text-blue-900">ELIGCRED</h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="space-x-4">
                            <a href="{{ url('/admin/login') }}" class="text-gray-700 hover:text-blue-900">Connexion</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Section Héro -->
        <div class="gradient">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold text-white sm:text-5xl sm:tracking-tight lg:text-6xl">
                        Évaluation de Crédit Intelligente
                    </h1>
                    <p class="mt-6 max-w-2xl mx-auto text-xl text-blue-100">
                        Optimisez vos décisions de crédit avec notre système d'évaluation basé sur l'intelligence artificielle.
                    </p>
                    <div class="mt-10">
                        <a href="{{ url('/admin/login') }}" class="inline-block bg-white text-blue-900 px-8 py-3 rounded-md text-lg font-medium hover:bg-blue-50">
                            Commencer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Fonctionnalités -->
        <div class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Fonctionnalités Principales
                    </h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Découvrez comment notre solution peut transformer votre processus d'évaluation de crédit
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Fonctionnalité 1 -->
                    <div class="feature-card bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-900 text-4xl mb-4">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Évaluation par IA</h3>
                        <p class="text-gray-600">
                            Analyse intelligente des dossiers de crédit avec des modèles d'IA avancés.
                        </p>
                    </div>

                    <!-- Fonctionnalité 2 -->
                    <div class="feature-card bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-900 text-4xl mb-4">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Analyse Financière</h3>
                        <p class="text-gray-600">
                            Évaluation complète des ratios financiers et de la capacité de remboursement.
                        </p>
                    </div>

                    <!-- Fonctionnalité 3 -->
                    <div class="feature-card bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-900 text-4xl mb-4">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Gestion des Dossiers</h3>
                        <p class="text-gray-600">
                            Suivi et gestion efficace de tous vos dossiers de crédit en un seul endroit.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <footer class="bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-gray-400">
                        © {{ date('Y') }} ELIGCRED. Tous droits réservés.
                    </p>
                </div>
            </div>
        </footer>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </body>
</html>
