// // sidebar.js
// document.addEventListener('DOMContentLoaded', function() {
//     const sidebar = document.getElementById('sidebar');
//     const toggleBtn = document.getElementById('sidebarToggle');

//     toggleBtn.addEventListener('click', function() {
//         sidebar.classList.toggle('close');
//     });
// });


// // JavaScript to handle dropdowns
// document.querySelectorAll('.dropdown-btn').forEach(button => {
//     button.addEventListener('click', function() {
//         // Close all other dropdowns
//         document.querySelectorAll('.dropdown-menu').forEach(menu => {
//             if (menu !== this.nextElementSibling) {
//                 menu.classList.remove('show');
//                 // Rotate the arrowhead icon to point right
//                 menu.previousElementSibling.querySelector('i.fa-chevron-right').classList.remove('fa-rotate-180');
//             }
//         });

//         // Toggle the clicked dropdown
//         const menu = this.nextElementSibling;
//         menu.classList.toggle('show');
//         // Rotate the arrowhead icon
//         this.querySelector('i.fa-chevron-right').classList.toggle('fa-rotate-180');
//     });
// });
