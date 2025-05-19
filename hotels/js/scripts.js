document.addEventListener('DOMContentLoaded', function() {
  // Form validation
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Simple validation
      const name = document.getElementById('name').value;
      const email = document.getElementById('email').value;
      const checkIn = document.getElementById('checkIn').value;
      const checkOut = document.getElementById('checkOut').value;
      
      if (!name || !email || !checkIn || !checkOut) {
        alert('Please fill in all required fields');
        return;
      }
      
      // If validation passes
      alert('Booking submitted successfully! We will contact you shortly.');
      this.reset();
    });
  }
  
  // Date picker initialization
  const dateInputs = document.querySelectorAll('input[type="date"]');
  dateInputs.forEach(input => {
    const today = new Date().toISOString().split('T')[0];
    input.min = today;
    
    // Set checkout min date based on checkin date
    if (input.id === 'checkOut') {
      document.getElementById('checkIn').addEventListener('change', function() {
        input.min = this.value;
      });
    }
  });
  
  // Room type selection effect
  const roomOptions = document.querySelectorAll('.room-option');
  if (roomOptions.length > 0) {
    roomOptions.forEach(option => {
      option.addEventListener('click', function() {
        roomOptions.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('roomType').value = this.dataset.value;
      });
    });
  }
  
  // Animation triggers
  const animatedElements = document.querySelectorAll('.animated');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = 1;
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });
  
  animatedElements.forEach(el => observer.observe(el));
});