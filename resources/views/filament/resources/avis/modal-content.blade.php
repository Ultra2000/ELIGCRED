<div class="space-y-4">
    <div class="p-4 bg-gray-50 rounded-lg mb-4">
        <h3 class="font-medium text-lg mb-2">Informations du dossier</h3>
        <div class="grid grid-cols-2 gap-4">
            <div><span class="font-medium">Numéro:</span> {{ $dossier->numero_dossier }}</div>
            <div><span class="font-medium">Client:</span> {{ $dossier->nom_client }} {{ $dossier->prenom_client }}</div>
            <div><span class="font-medium">Montant sollicité:</span> {{ number_format($dossier->montant_sollicite, 0, ',', ' ') }} XOF</div>
            <div><span class="font-medium">Statut:</span> {{ $dossier->statut }}</div>
        </div>
    </div>
    
    <h3 class="font-medium text-lg mb-2">Tous les avis</h3>
    @foreach($avis as $unAvis)
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="font-medium">{{ $unAvis->user->name }}</div>
            <div class="text-sm text-gray-600">Avis: {{ $unAvis->avis === 'FAVORABLE' ? 'Favorable' : 'Non favorable' }}</div>
            @if($unAvis->observations)
                <div class="mt-2 text-sm">{{ $unAvis->observations }}</div>
            @endif
            <div class="text-xs text-gray-500 mt-1">{{ $unAvis->created_at->format('d/m/Y H:i') }}</div>
        </div>
    @endforeach
</div> 