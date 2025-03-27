// animations.js
document.addEventListener('DOMContentLoaded', function() {
    // Button ripple effect
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Only prevent default if it's not a link
            if (!this.href) e.preventDefault();
            
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
            
            // If it's a real link, follow it after animation
            if(this.href && !this.classList.contains('no-redirect')) {
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            }
        });
    });
    
    // Input field animations
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.boxShadow = '0 0 0 3px rgba(255, 75, 43, 0.3)';
            this.parentElement.style.borderColor = 'var(--secondary-color)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.boxShadow = 'none';
            this.parentElement.style.borderColor = '#eee';
        });
    });
});