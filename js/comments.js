function toggleComments(postId, event) {
  const box = document.getElementById("comments-" + postId);
  if (box) {
    box.style.display = box.style.display === "none" ? "block" : "none";
  }
}

function submitComment(event) {
  event.preventDefault();
  const form = event.target;
  const postId = form.dataset.postId || form.querySelector("[name=\"post_id\"]").value;
  const formData = new FormData(form);
  fetch("post_handler.php", {method: "POST", body: formData})
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const box = document.getElementById("comments-" + postId);
        const list = box.querySelector(".comments-list");
        const input = form.querySelector("[name=\"comment\"]");
        list.insertAdjacentHTML("beforeend", `
          <div class="comment-item mb-2 p-2 bg-light rounded">
            <small class="fw-bold text-primary">${input.dataset.username || 'Anonymous'}</small>
            <div>${input.value}</div>
          </div>`);
        input.value = "";
        const btns = document.querySelectorAll(".comment-btn");
        btns.forEach(btn => {
          if (btn.textContent.includes(postId)) {
            btn.innerHTML = `💬 Comment (${list.children.length})`;
          }
        });
      }
    }).catch(console.error);
}
