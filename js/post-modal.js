// Post Create Modal JS
document.addEventListener('DOMContentLoaded', function() {
  window.openPostModal = function() {
    const modalEl = document.getElementById('postCreateModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      // Reset form
      const form = document.getElementById('postCreateForm');
      form.reset();
      document.querySelector('.image-preview-container').classList.add('d-none');
      document.getElementById('modalTextarea').style.height = 'auto';
      modal.show();
    }
  };
  const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('postCreateModal'));
  const textarea = document.getElementById('modalTextarea');
  const fileInput = document.querySelector('#postCreateForm input[type="file"]');
  const preview = document.getElementById('imagePreview');
  const previewContainer = document.querySelector('.image-preview-container');
  const form = document.getElementById('postCreateForm');

  // Autosize textarea
  textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
  });

  // Image preview
  fileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        previewContainer.classList.remove('d-none');
      };
      reader.readAsDataURL(file);
    }
  });

  // Submit post AJAX
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(form);
    fetch('post_handler.php', {
      method: 'POST',
      body: formData
    }).then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload(); // Reload to show new post
        }
      }).catch(error => console.error('Post error:', error));
  });

  // Close/reset modal
  document.getElementById('postCreateModal').addEventListener('hidden.bs.modal', function() {
    form.reset();
    previewContainer.classList.add('d-none');
    textarea.style.height = 'auto';
  });
});
