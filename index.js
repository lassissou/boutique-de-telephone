document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du slider
    initSlider();
    
    // Animation au défilement
    setupScrollAnimations();
    
    // Gestion du menu mobile
    setupMobileMenu();
    
    // Animation de chargement
    showLoading();
  });
  
  // Slider de bannière
  function initSlider() {
    const slider = document.querySelector('.bannier-slider');
    const slides = document.querySelectorAll('.slider-image');
    const dots = document.querySelectorAll('.slider-dot');
    let currentIndex = 0;
    let slideInterval;
    let isAnimating = false;
  
    if (!slides.length) return;
  
    function showSlide(index) {
      if (isAnimating) return;
      isAnimating = true;
      
      // Retirer les classes actives
      slides.forEach(slide => slide.classList.remove('active'));
      dots.forEach(dot => dot.classList.remove('active'));
      
      // Ajouter la classe active au slide et dot correspondant
      slides[index].classList.add('active');
      dots[index].classList.add('active');
      
      // Réinitialiser l'animation
      setTimeout(() => {
        isAnimating = false;
      }, 1000);
    }
  
    function nextSlide() {
      currentIndex = (currentIndex + 1) % slides.length;
      showSlide(currentIndex);
    }
  
    function startSlider() {
      slideInterval = setInterval(nextSlide, 5000);
    }
  
    function pauseSlider() {
      clearInterval(slideInterval);
    }
  
    // Navigation par points
    dots.forEach(dot => {
      dot.addEventListener('click', function() {
        currentIndex = parseInt(this.getAttribute('data-index'));
        showSlide(currentIndex);
        pauseSlider();
        startSlider();
      });
    });
  
    // Navigation clavier
    document.addEventListener('keydown', function(e) {
      if (e.key === 'ArrowLeft') {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(currentIndex);
      } else if (e.key === 'ArrowRight') {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
      }
    });
  
    // Démarrer le slider
    showSlide(currentIndex);
    startSlider();
  
    // Pause au survol
    const banner = document.querySelector('.banner');
    if (banner) {
      banner.addEventListener('mouseenter', pauseSlider);
      banner.addEventListener('mouseleave', startSlider);
      banner.addEventListener('touchstart', pauseSlider);
      banner.addEventListener('touchend', startSlider);
    }
  }
  
  // Animations au défilement
  function setupScrollAnimations() {
    const animateOnScroll = (elements, className) => {
      elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (elementPosition < screenPosition) {
          element.classList.add(className);
        }
      });
    };
    
    const animatedElements = document.querySelectorAll('.produit, .promotion, .avis-card, section');
    
    window.addEventListener('scroll', () => {
      animateOnScroll(animatedElements, 'animate');
    });
    
    // Initial check in case elements are already visible
    animateOnScroll(animatedElements, 'animate');
  }
  
  // Menu mobile
  function setupMobileMenu() {
    const menuToggle = document.createElement('div');
    menuToggle.className = 'mobile-menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    menuToggle.style.display = 'none';
  
    const header = document.querySelector('header');
    if (header) {
      header.prepend(menuToggle);
      
      const nav = document.querySelector('nav');
      
      menuToggle.addEventListener('click', () => {
        nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
      });
      
      window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
          nav.style.display = '';
          menuToggle.style.display = 'none';
        } else {
          menuToggle.style.display = 'block';
          nav.style.display = 'none';
        }
      });
      
      if (window.innerWidth <= 768) {
        menuToggle.style.display = 'block';
        nav.style.display = 'none';
      }
    }
  }
  
  // Animation de chargement
  function showLoading() {
    const loader = document.createElement('div');
    loader.className = 'page-loader';
    loader.innerHTML = `
      <div class="loader-spinner">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    `;
    document.body.appendChild(loader);
    
    window.addEventListener('load', () => {
      setTimeout(() => {
        loader.style.opacity = '0';
        setTimeout(() => {
          loader.remove();
        }, 500);
      }, 1000);
    });
  }