function toggleComments(postId, event) {
  const box = document.getElementById("comments-" + postId);
  if (box) {
    box.style.display = box.style.display === "none" ? "block" : "none";
  }
}

function submitComment(event, postId) {
  event.preventDefault();
  const form = event.target;
  const actualPostId = postId || form.querySelector("[name=\"post_id\"]").value;
  const commentInput = form.querySelector("[name=\"comment\"]");
  const commentText = commentInput.value.trim();
  
  if (!commentText) return;
  
  const formData = new FormData(form);
  fetch("post_handler.php", {method: "POST", body: formData})
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const box = document.getElementById("comments-" + actualPostId);
        if (box) {
          const list = box.querySelector(".comments-list");
          if (list) {
            const userName = document.querySelector('.user-avatar') ? 'You' : 'Anonymous';
            const newComment = document.createElement('div');
            newComment.className = 'comment-item mb-2 p-2 bg-light rounded';
            newComment.innerHTML = `
              <small class="fw-bold text-primary">${userName}</small>
              <div>${commentText}</div>
            `;
            list.appendChild(newComment);
            commentInput.value = "";
            
            // Update comment count button
            const commentBtn = document.querySelector('.comment-btn[data-post-id="' + actualPostId + '"]');
            if (commentBtn) {
              const match = commentBtn.textContent.match(/\((\d+)\)/);
              const currentCount = match ? parseInt(match[1]) : 0;
              commentBtn.textContent = commentBtn.textContent.replace(/\(\d+\)/, "(" + (currentCount + 1) + ")");
            }
          }
        }
      }
    }).catch(console.error);
}
