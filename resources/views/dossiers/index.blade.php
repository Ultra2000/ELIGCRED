<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <button onclick="evaluateDossier({{ $dossier->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
        Évaluer IA
    </button>
    <a href="{{ route('dossiers.edit', $dossier) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</a>
    <form action="{{ route('dossiers.destroy', $dossier) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?')">Supprimer</button>
    </form>
</td>

@push('scripts')
<script>
function evaluateDossier(dossierId) {
    if (confirm('Voulez-vous évaluer ce dossier avec l\'IA ?')) {
        fetch(`/dossiers/${dossierId}/evaluate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Évaluation effectuée avec succès !\nScore IA : ' + data.data.score + '%\nStatut : ' + data.data.statut);
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de l\'évaluation');
        });
    }
}
</script>
@endpush 