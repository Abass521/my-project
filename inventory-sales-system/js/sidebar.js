document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.createElement('button');
    toggleBtn.textContent = "☰";
    toggleBtn.className = "sidebar-toggle";
    document.body.appendChild(toggleBtn);
  
    const sidebar = document.querySelector('.sidebar');
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  });
  