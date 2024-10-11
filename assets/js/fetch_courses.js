function fetchCourses(category) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'admin/admission/fetch_courses.php?category=' + encodeURIComponent(category), true);
    xhr.onload = function() {
        if (this.status == 200) {
            var courses = JSON.parse(this.responseText);
            var courseDropdown = document.getElementById('courseDropdown');
            courseDropdown.innerHTML = '<option value="">Select Course</option>';
            courses.forEach(function(course) {
                var option = document.createElement('option');
                option.value = course.CourseName;
                option.textContent = course.CourseName;
                courseDropdown.appendChild(option);
            });
        }
    };
    xhr.send();
}
