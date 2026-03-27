document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.profile-tab');
    const contents = document.querySelectorAll('.tab-content');

    function showTab(tabId) {
      contents.forEach(c => c.classList.remove('active'));
      tabs.forEach(t => t.classList.remove('active'));

      const activeContent = document.getElementById(tabId + '-tab');
      const activeTab = Array.from(tabs).find(t => t.dataset.tab === tabId);

      if (activeContent) activeContent.classList.add('active');
      if (activeTab) activeTab.classList.add('active');
    }

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        showTab(tab.dataset.tab);
      });
    });

    // Show posts tab by default on load
    showTab('posts');
  });
