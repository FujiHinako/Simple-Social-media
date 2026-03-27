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

  // Dynamic post update functions
  window.addPost = function(postData) {
    const avatar = postData.user_name ? postData.user_name.substring(0,2).toUpperCase() : 'UN';
    const time = new Date(postData.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
    const content = postData.content.replace(/&/g,'&amp;').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"');
    const likes = postData.likes || 0;
    const imageHtml = postData.image ? `<img src="${postData.image}" alt="Post image" class="img-fluid rounded mt-3 post-image" style="max-height:400px; object-fit:cover;">` : '';
    
    const postHtml = `
      <article class="post-card">
        <div class="post-header">
          <a href="profile.php?uid=${postData.user_id}" class="post-user">
            <div class="post-avatar">${avatar}</div>
            <div>
              <div class="post-author">${postData.user_name}</div>
              <div class="post-time">${time}</div>
            </div>
          </a>
        </div>
        <div class="post-content">${content}</div>
        ${imageHtml}
        <div class="post-actions">
          <button class="like-btn" onclick="likePost('${postData.id}')">👍 ${likes}</button>
          <button class="comment-btn" onclick="showComments('${postData.id}')">💬 Comment</button>
        </div>
        <div id="comments-${postData.id}" style="display:none;" class="border-top pt-3">
          <form method="POST" action="post_handler.php" class="d-flex gap-2">
            <input type="hidden" name="post_id" value="${postData.id}">
            <input type="text" name="comment" class="form-control form-control-sm" placeholder="Write a comment..." required maxlength="500">
            <button type="submit" class="btn btn-primary btn-sm">Post</button>
          </form>
        </div>
      </article>
    `;
    
    const container = document.querySelector('#posts') || document.querySelector('.tab-content.active') || document.querySelector('.posts-section');
    if (container) {
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = postHtml;
      container.prepend(tempDiv.firstElementChild);
    }
  };

  window.showConfirmation = function() {
    const toastHtml = `
      <div id="post-success-toast" class="alert alert-success position-fixed translate-middle-y m-3 end-0 top-50 shadow" style="z-index: 9999; max-width: 350px;" role="alert">
        <i class="fas fa-check-circle me-2"></i>Post created successfully!
        <button type="button" class="btn-close ms-2" data-mdb-dismiss="alert"></button>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    setTimeout(() => {
      const toast = document.getElementById('post-success-toast');
      if (toast) toast.remove();
    }, 4000);
  };

  // Submit post AJAX
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(form);
    fetch('post_handler.php', {
      method: 'POST',
      body: formData
    }).then(response => response.json())
      .then(data => {
        if (data.success && data.post) {
          addPost(data.post);
          showConfirmation();
          modal.hide();
          form.reset();
          previewContainer.classList.add('d-none');
          textarea.style.height = 'auto';
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
