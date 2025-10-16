// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize Application
function initializeApp() {
    setupNavigation();
    setupSearchFunctionality();
    setupCarousels();
    setupFAQ();
    setupScrollEffects();
    setupNewsletter();
    setupBackToTop();
    setupAnimations();
    setupPlaceFiltering();
}

// Navigation Setup
function setupNavigation() {
    const navbar = document.getElementById('navbar');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    const inspireBtn = document.getElementById('inspireBtn');
    const inspireDropdown = document.getElementById('inspireDropdown');

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close mobile menu when clicking on links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    // Inspire dropdown
    if (inspireBtn && inspireDropdown) {
        inspireBtn.addEventListener('click', (e) => {
            e.preventDefault();
            inspireDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!inspireBtn.contains(e.target) && !inspireDropdown.contains(e.target)) {
                inspireDropdown.classList.remove('active');
            }
        });
    }
}

// Search Functionality
function setupSearchFunctionality() {
    const searchInput = document.getElementById('destinationSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const budgetFilter = document.getElementById('budgetFilter');
    const searchBtn = document.querySelector('.btn-search');
    const placesGrid = document.getElementById('placesGrid');

    // Search functionality
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const selectedBudget = budgetFilter.value;

        const placeCards = document.querySelectorAll('.place-card');
        
        placeCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const location = card.querySelector('p').textContent.toLowerCase();
            const cardCategory = card.dataset.category;
            const cardBudget = card.dataset.budget;

            const matchesSearch = searchTerm === '' || 
                title.includes(searchTerm) || 
                location.includes(searchTerm);
            
            const matchesCategory = selectedCategory === '' || cardCategory === selectedCategory;
            const matchesBudget = selectedBudget === '' || cardBudget === selectedBudget;

            if (matchesSearch && matchesCategory && matchesBudget) {
                card.style.display = 'block';
                card.style.animation = 'fadeInUp 0.5s ease-out';
            } else {
                card.style.display = 'none';
            }
        });

        // Show no results message if needed
        const visibleCards = Array.from(placeCards).filter(card => 
            card.style.display !== 'none'
        );

        let noResultsMsg = document.getElementById('no-results');
        if (visibleCards.length === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'no-results';
                noResultsMsg.className = 'no-results';
                noResultsMsg.innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No destinations found</h3>
                        <p>Try adjusting your search criteria or browse all destinations</p>
                    </div>
                `;
                placesGrid.appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }

    // Event listeners
    searchInput.addEventListener('input', debounce(performSearch, 300));
    categoryFilter.addEventListener('change', performSearch);
    budgetFilter.addEventListener('change', performSearch);
    searchBtn.addEventListener('click', (e) => {
        e.preventDefault();
        performSearch();
    });

    // Clear search
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchInput.value = '';
            categoryFilter.value = '';
            budgetFilter.value = '';
            performSearch();
        }
    });
}

// Place Filtering
function setupPlaceFiltering() {
    const placesGrid = document.getElementById('placesGrid');
    if (!placesGrid) return;

    // Add filter buttons
    const filterContainer = document.createElement('div');
    filterContainer.className = 'filter-buttons';
    filterContainer.style.cssText = `
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    `;

    const filters = [
        { key: 'all', label: 'All', icon: 'fas fa-globe' },
        { key: 'city', label: 'Cities', icon: 'fas fa-city' },
        { key: 'nature', label: 'Nature', icon: 'fas fa-mountain' },
        { key: 'beach', label: 'Beaches', icon: 'fas fa-umbrella-beach' },
        { key: 'culture', label: 'Culture', icon: 'fas fa-landmark' },
        { key: 'mountain', label: 'Mountains', icon: 'fas fa-mountain' }
    ];

    filters.forEach(filter => {
        const button = document.createElement('button');
        button.className = `filter-btn ${filter.key === 'all' ? 'active' : ''}`;
        button.innerHTML = `<i class="${filter.icon}"></i> ${filter.label}`;
        button.style.cssText = `
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--border-color);
            background: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        `;

        button.addEventListener('click', () => {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(btn => 
                btn.classList.remove('active')
            );
            // Add active class to clicked button
            button.classList.add('active');
            button.style.background = 'var(--primary-color)';
            button.style.color = 'white';
            button.style.borderColor = 'var(--primary-color)';

            // Filter places
            filterPlaces(filter.key);
        });

        filterContainer.appendChild(button);
    });

    // Insert filter buttons before places grid
    placesGrid.parentNode.insertBefore(filterContainer, placesGrid);

    // Add CSS for filter buttons
    const style = document.createElement('style');
    style.textContent = `
        .filter-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .filter-btn.active {
            background: var(--primary-color) !important;
            color: white !important;
            border-color: var(--primary-color) !important;
        }
    `;
    document.head.appendChild(style);
}

function filterPlaces(category) {
    const placeCards = document.querySelectorAll('.place-card');
    
    placeCards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.5s ease-out';
        } else {
            card.style.display = 'none';
        }
    });
}

// Carousel Setup
function setupCarousels() {
    setupDealsCarousel();
    setupTestimonialsCarousel();
}

function setupDealsCarousel() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.deal-slide');
    const dots = document.querySelectorAll('.carousel-dots .dot');

    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        if (n >= slides.length) currentSlide = 0;
        if (n < 0) currentSlide = slides.length - 1;

        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        currentSlide++;
        showSlide(currentSlide);
    }

    // Auto-advance slides
    setInterval(nextSlide, 5000);

    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });

    // Make currentSlide function global for HTML onclick
    window.currentSlide = (n) => {
        currentSlide = n - 1;
        showSlide(currentSlide);
    };
}

function setupTestimonialsCarousel() {
    let currentTestimonial = 0;
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.testimonial-dots .dot');

    function showTestimonial(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        if (n >= slides.length) currentTestimonial = 0;
        if (n < 0) currentTestimonial = slides.length - 1;

        slides[currentTestimonial].classList.add('active');
        dots[currentTestimonial].classList.add('active');
    }

    function nextTestimonial() {
        currentTestimonial++;
        showTestimonial(currentTestimonial);
    }

    // Auto-advance testimonials
    setInterval(nextTestimonial, 6000);

    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentTestimonial = index;
            showTestimonial(currentTestimonial);
        });
    });

    // Make currentTestimonial function global for HTML onclick
    window.currentTestimonial = (n) => {
        currentTestimonial = n - 1;
        showTestimonial(currentTestimonial);
    };
}

// FAQ Setup
function setupFAQ() {
    const faqQuestions = document.querySelectorAll('.faq-question');

    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const isActive = question.classList.contains('active');

            // Close all other FAQs
            faqQuestions.forEach(q => {
                q.classList.remove('active');
                q.nextElementSibling.classList.remove('open');
            });

            // Toggle current FAQ
            if (!isActive) {
                question.classList.add('active');
                answer.classList.add('open');
            }
        });
    });
}

// Scroll Effects
function setupScrollEffects() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.place-card, .package-card, .wonder-card, .testimonial-content').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Newsletter Setup
function setupNewsletter() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[type="email"]').value;
            
            if (validateEmail(email)) {
                // Simulate newsletter subscription
                showNotification('Thank you for subscribing! You\'ll receive our latest travel deals soon.', 'success');
                this.reset();
            } else {
                showNotification('Please enter a valid email address.', 'error');
            }
        });
    }
}

// Back to Top Button
function setupBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Animations Setup
function setupAnimations() {
    // Parallax effect for hero section
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.hero-overlay');
        if (parallax) {
            const speed = scrolled * 0.5;
            parallax.style.transform = `translateY(${speed}px)`;
        }
    });

    // Counter animation
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    });

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

// Counter Animation
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000; // 2 seconds
    const increment = target / (duration / 16); // 60fps
    let current = 0;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        max-width: 300px;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Smooth scroll to section
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .notification {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
    }
`;
document.head.appendChild(style);

// Image lazy loading
function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Performance optimization
function optimizePerformance() {
    // Preload critical images
    const criticalImages = [
        'places/Eiffel Tower.jpg',
        'places/Statue of Liberty.jpeg',
        'places/Sydney_Opera_House.jpeg'
    ];

    criticalImages.forEach(src => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = src;
        document.head.appendChild(link);
    });

    // Setup lazy loading
    setupLazyLoading();
}

// Initialize performance optimizations
document.addEventListener('DOMContentLoaded', optimizePerformance);

// Add keyboard navigation support
document.addEventListener('keydown', (e) => {
    // ESC key closes modals/dropdowns
    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown-content.active').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
        document.querySelectorAll('.faq-question.active').forEach(question => {
            question.classList.remove('active');
            question.nextElementSibling.classList.remove('open');
        });
    }
});

// Add touch/swipe support for carousels
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;

    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe left - next slide
            const activeSlide = document.querySelector('.deal-slide.active');
            if (activeSlide) {
                const nextSlide = activeSlide.nextElementSibling || 
                    activeSlide.parentElement.firstElementChild;
                if (nextSlide) {
                    activeSlide.classList.remove('active');
                    nextSlide.classList.add('active');
                }
            }
        } else {
            // Swipe right - previous slide
            const activeSlide = document.querySelector('.deal-slide.active');
            if (activeSlide) {
                const prevSlide = activeSlide.previousElementSibling || 
                    activeSlide.parentElement.lastElementChild;
                if (prevSlide) {
                    activeSlide.classList.remove('active');
                    prevSlide.classList.add('active');
                }
            }
        }
    }
}

// Add loading states
function showLoading(element) {
    element.style.opacity = '0.6';
    element.style.pointerEvents = 'none';
}

function hideLoading(element) {
    element.style.opacity = '1';
    element.style.pointerEvents = 'auto';
}

// Add error handling for images
document.addEventListener('DOMContentLoaded', () => {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', () => {
            img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIG5vdCBmb3VuZDwvdGV4dD48L3N2Zz4=';
            img.alt = 'Image not found';
        });
    });
});

// Export functions for global access
window.scrollToSection = scrollToSection;
window.currentSlide = currentSlide;
window.currentTestimonial = currentTestimonial;