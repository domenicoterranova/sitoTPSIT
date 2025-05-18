/**
 * AudioWear - Script di funzionalità JavaScript personalizzate
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inizializzazione tooltip di Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestione delle quantità nel carrello
    const quantityInputs = document.querySelectorAll('input[name="quantity"]');
    if (quantityInputs) {
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const max = parseInt(this.getAttribute('max'));
                const min = parseInt(this.getAttribute('min'));
                const value = parseInt(this.value);

                if (value > max) {
                    this.value = max;
                    alert('La quantità richiesta supera la disponibilità.');
                } else if (value < min) {
                    this.value = min;
                }
            });
        });
    }

    // Gestione delle stelle di valutazione nelle recensioni
    const ratingInputs = document.querySelectorAll('.btn-check[name="rating"]');
    if (ratingInputs) {
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const value = this.value;
                const stars = document.querySelectorAll('.rating-stars .btn-outline-warning i');
                
                stars.forEach((star, index) => {
                    if (index < value) {
                        star.className = 'fas fa-star';
                    } else {
                        star.className = 'far fa-star';
                    }
                });
            });
        });
    }

    // Animazione per il ritorno in cima alla pagina
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Gestione immagini del prodotto (se presenti)
    const productThumbnails = document.querySelectorAll('.product-thumbnail');
    if (productThumbnails.length > 0) {
        const mainImage = document.getElementById('product-main-image');
        
        productThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const imageSrc = this.getAttribute('data-image-src');
                mainImage.src = imageSrc;
                
                // Rimuovi la classe 'active' da tutte le thumbnails
                productThumbnails.forEach(thumb => thumb.classList.remove('active'));
                
                // Aggiungi la classe 'active' alla thumbnail selezionata
                this.classList.add('active');
            });
        });
    }

    // Validazione form di checkout
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            if (!checkoutForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            checkoutForm.classList.add('was-validated');
        });
    }
});