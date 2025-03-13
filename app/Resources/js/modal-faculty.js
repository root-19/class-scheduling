    // Open modal function
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    
    // Close modal function
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
    
    // Edit faculty function
    function editFaculty(id, facultyId, name, email, contact,address) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_faculty_id').value = facultyId;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_contact').value = contact;
        document.getElementById('edit_address').value = address;

        openModal('editFacultyModal');
    }