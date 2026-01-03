/**
 * Preview d'images pour MediaField et gestion du checkbox delete
 * Affiche une preview de l'image sélectionnée avant l'upload
 */
document.addEventListener('DOMContentLoaded', function() {
    // Trouve tous les champs MediaField
    const mediaFields = document.querySelectorAll('.field-media input[type="file"]');

    mediaFields.forEach(function(fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (!file) {
                return;
            }

            // Vérifie que c'est une image
            if (!file.type.startsWith('image/')) {
                return;
            }

            // Trouve le conteneur parent
            const fieldWrapper = fileInput.closest('.media-upload-field');
            if (!fieldWrapper) {
                return;
            }

            // Trouve ou crée la zone de preview
            let previewDiv = fieldWrapper.querySelector('.new-media-preview');
            if (!previewDiv) {
                previewDiv = document.createElement('div');
                previewDiv.className = 'new-media-preview mt-2 mb-2';
                previewDiv.innerHTML = '<label class="form-label">Nouvelle image (preview):</label>' +
                    '<div class="preview-image-container"></div>' +
                    '<div class="text-muted small mt-1">' +
                    '<span class="preview-filename"></span> ' +
                    '(<span class="preview-filesize"></span>)' +
                    '</div>';

                // Insère après le champ d'upload
                const formGroup = fileInput.closest('.form-group');
                if (formGroup) {
                    formGroup.appendChild(previewDiv);
                }
            }

            // Affiche les infos du fichier
            const filenameSpan = previewDiv.querySelector('.preview-filename');
            const filesizeSpan = previewDiv.querySelector('.preview-filesize');
            const imageContainer = previewDiv.querySelector('.preview-image-container');

            filenameSpan.textContent = file.name;
            filesizeSpan.textContent = formatFileSize(file.size);

            // Crée la preview de l'image
            const reader = new FileReader();
            reader.onload = function(event) {
                imageContainer.innerHTML = '<img src="' + event.target.result + '" ' +
                    'alt="Preview" ' +
                    'class="img-thumbnail" ' +
                    'style="max-width: 200px; max-height: 200px;">';
            };
            reader.readAsDataURL(file);
        });
    });

    // Gestion du checkbox "Supprimer l'image existante"
    const deleteCheckboxes = document.querySelectorAll('.media-upload-field input[type="checkbox"]');

    deleteCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function(e) {
            const fieldWrapper = checkbox.closest('.media-upload-field');
            if (!fieldWrapper) {
                return;
            }

            const fileInput = fieldWrapper.querySelector('input[type="file"]');
            const currentPreview = fieldWrapper.querySelector('.current-media-preview');

            if (checkbox.checked) {
                // Si "delete" est coché, désactive l'upload et griser la preview
                if (fileInput) {
                    fileInput.disabled = true;
                }
                if (currentPreview) {
                    currentPreview.style.opacity = '0.5';
                    currentPreview.style.textDecoration = 'line-through';
                }
            } else {
                // Si "delete" est décoché, réactive l'upload et la preview
                if (fileInput) {
                    fileInput.disabled = false;
                }
                if (currentPreview) {
                    currentPreview.style.opacity = '1';
                    currentPreview.style.textDecoration = 'none';
                }
            }
        });
    });

    /**
     * Formate la taille du fichier en KB/MB
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
});
