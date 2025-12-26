document.getElementById('cultural-decor-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('booking_data', JSON.stringify(saveFormData()));
    fetch('process_cultural_decor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('booking-form').reset();
            document.getElementById('additional-form').reset();
            document.getElementById('logistics-form').reset();
            document.getElementById('cultural-decor-form').reset();
            showPage1();
            alert('Booking submitted successfully!');
            // Redirect to printable page
            window.open(`print_booking.php?booking_id=${data.booking_id}`, '_blank');
        } else {
            alert('Error submitting booking: ' + data.message);
        }
    })
    .catch(error => alert('Error: ' + error));
});