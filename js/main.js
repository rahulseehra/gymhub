/**
 * GymHub - Main JavaScript File
 * public/js/main.js
 *
 * This single file handles all the interactive behaviour across the entire site.
 * It is loaded at the bottom of every page via footer.php.
 *
 * What this file does:
 *   1. Mobile hamburger menu — opens and closes the nav on small screens
 *   2. Sticky header shadow — adds a shadow when the user scrolls down
 *   3. Animated counters — counts up numbers on the homepage hero section
 *   4. Video lightbox — opens and closes the promo video popup
 *   5. Scroll-reveal animations — fades cards in as the user scrolls down
 *   6. Flash message auto-dismiss — fades out success/error alerts after 4 seconds
 *   7. Star rating input — lets users click stars to select a rating
 *
 * No external libraries are used — this is all plain JavaScript.
 */

'use strict'; // strict mode catches common JavaScript mistakes early

/* ══════════════════════════════════════════════════════════
   1. MOBILE HAMBURGER MENU
   Makes the nav work on small screens. The nav links are
   hidden by default on mobile, and shown when the hamburger
   button is clicked. The button also animates into an X.
   ══════════════════════════════════════════════════════════ */

// Get references to the hamburger button and the nav links list
const hamburger = document.getElementById('nav-hamburger');
const navLinks  = document.getElementById('nav-links');

if (hamburger && navLinks) {

    // When the hamburger button is clicked, toggle the 'open' class on nav links
    hamburger.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('open'); // true if just opened
        hamburger.setAttribute('aria-expanded', isOpen); // accessibility

        // Animate the three hamburger lines into an X shape when open
        const spans = hamburger.querySelectorAll('span');
        if (isOpen) {
            spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';  // top line
            spans[1].style.opacity   = '0';                                   // middle line disappears
            spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)'; // bottom line
        } else {
            // Reset back to hamburger shape when closed
            spans[0].style.transform = '';
            spans[1].style.opacity   = '';
            spans[2].style.transform = '';
        }
    });

    // If the user clicks anywhere outside the menu, close it
    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });
}

/* ══════════════════════════════════════════════════════════
   2. STICKY HEADER SHADOW ON SCROLL
   Adds a subtle shadow under the sticky header when the user
   scrolls down, so it visually separates from the page content.
   ══════════════════════════════════════════════════════════ */

const siteHeader = document.getElementById('site-header');

if (siteHeader) {
    window.addEventListener('scroll', () => {
        // If user has scrolled more than 10px, add shadow
        if (window.scrollY > 10) {
            siteHeader.style.boxShadow = '0 4px 20px rgba(0,0,0,0.4)';
        } else {
            siteHeader.style.boxShadow = 'none'; // remove shadow at the top
        }
    }, { passive: true }); // passive: true improves scroll performance
}

/* ══════════════════════════════════════════════════════════
   3. ANIMATED NUMBER COUNTERS
   On the homepage hero, the stats (Members, Classes, etc.)
   count up from 0 to their real value when they scroll into view.
   Elements need class="counter" and data-target="NUMBER".
   ══════════════════════════════════════════════════════════ */

/**
 * animateCounter(el) — Counts a number up from 0 to its target value
 * Uses setInterval to increment the number roughly 60 times per second
 * until it reaches the target, then clears itself.
 */
function animateCounter(el) {
    const target   = parseInt(el.getAttribute('data-target'), 10); // the final number
    const duration = 1500; // total animation time in milliseconds
    const step     = Math.ceil(target / (duration / 16)); // how much to add each frame
    let   current  = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;      // don't go over the target
            clearInterval(timer);  // stop the animation
        }
        el.textContent = current.toLocaleString(); // format with commas e.g. 1,000
    }, 16); // ~60fps
}

// Use IntersectionObserver to only animate when the counter scrolls into view
// This is better than animating immediately on page load
const counters = document.querySelectorAll('.counter');

if (counters.length > 0 && 'IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);   // start counting
                counterObserver.unobserve(entry.target); // only animate once
            }
        });
    }, { threshold: 0.5 }); // trigger when 50% of the element is visible

    counters.forEach(counter => counterObserver.observe(counter));
}

/* ══════════════════════════════════════════════════════════
   4. VIDEO LIGHTBOX
   On the homepage, clicking "Watch the Video" opens a fullscreen
   popup overlay with the video playing inside it.
   Clicking the X button, the backdrop, or pressing Escape closes it.
   ══════════════════════════════════════════════════════════ */

const playBtn    = document.querySelector('.play-video-btn'); // the "Watch" button
const lightbox   = document.getElementById('video-lightbox'); // the popup overlay
const closeBtn   = document.getElementById('video-close-btn'); // the X button
const promoVideo = document.getElementById('promo-video');     // the actual video element

if (playBtn && lightbox) {

    // Open the lightbox when the play button is clicked
    playBtn.addEventListener('click', () => {
        lightbox.hidden = false;               // show the overlay
        document.body.style.overflow = 'hidden'; // prevent background scrolling
        if (promoVideo) promoVideo.play();     // start playing the video
    });

    // Close when the X button is clicked
    if (closeBtn) {
        closeBtn.addEventListener('click', closeVideoLightbox);
    }

    // Close when the user clicks on the dark backdrop (outside the video)
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeVideoLightbox();
    });

    // Close when the user presses the Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !lightbox.hidden) closeVideoLightbox();
    });

    // closeVideoLightbox — hides the popup and resets the video to the beginning
    function closeVideoLightbox() {
        lightbox.hidden = true;
        document.body.style.overflow = ''; // restore scrolling
        if (promoVideo) {
            promoVideo.pause();
            promoVideo.currentTime = 0; // rewind to start for next time
        }
    }
}

/* ══════════════════════════════════════════════════════════
   5. SCROLL-REVEAL ANIMATIONS
   Cards (class cards, membership cards, etc.) fade in and
   slide up slightly as the user scrolls them into view.
   This gives the page a polished, professional feel.
   ══════════════════════════════════════════════════════════ */

// Inject the CSS for the reveal animation into the page dynamically
// so we don't need a separate stylesheet for just these two rules
const revealStyle = document.createElement('style');
revealStyle.textContent = `
    .reveal {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.55s ease, transform 0.55s ease;
    }
    .reveal.revealed {
        opacity: 1;
        transform: translateY(0);
    }
`;
document.head.appendChild(revealStyle);

// Add the 'reveal' class to all cards so they start invisible
const revealTargets = document.querySelectorAll(
    '.class-card, .membership-card, .instructor-card, .testimonial-card, .category-card'
);

revealTargets.forEach((el, i) => {
    el.classList.add('reveal');
    // Stagger the animation delay so cards appear one after another
    // The % 6 means we reset after 6 cards (so we don't get huge delays)
    el.style.transitionDelay = `${(i % 6) * 0.07}s`;
});

// Watch each card and add the 'revealed' class when it enters the viewport
if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed'); // trigger the animation
                revealObserver.unobserve(entry.target);  // only animate once
            }
        });
    }, { threshold: 0.12 }); // trigger when 12% of the card is visible

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
}

/* ══════════════════════════════════════════════════════════
   6. FLASH MESSAGE AUTO-DISMISS
   Success and error alert messages (with class 'flash-message')
   automatically fade out after 4 seconds so they don't clutter the page.
   ══════════════════════════════════════════════════════════ */

document.querySelectorAll('.flash-message').forEach(msg => {
    setTimeout(() => {
        msg.style.transition = 'opacity 0.5s ease'; // smooth fade
        msg.style.opacity    = '0';
        // Remove the element from the DOM after the fade completes
        setTimeout(() => msg.remove(), 500);
    }, 4000); // wait 4 seconds before starting the fade
});

/* ══════════════════════════════════════════════════════════
   7. STAR RATING INPUT
   On the rate-class page, users click stars to select a rating.
   Hovering highlights stars up to the hovered one.
   Clicking locks in the selection and stores the value in a
   hidden input field that gets submitted with the form.
   ══════════════════════════════════════════════════════════ */

const starInputs = document.querySelectorAll('.star-rating-input .star');

if (starInputs.length > 0) {

    starInputs.forEach((star, index) => {

        // Hovering over a star highlights all stars up to that one
        star.addEventListener('mouseover', () => {
            highlightStars(index);
        });

        // Moving the mouse away resets to the currently selected rating
        star.addEventListener('mouseout', () => {
            const selected = document.querySelector('.star-rating-input').dataset.selected || 0;
            highlightStars(selected - 1); // -1 because index is 0-based
        });

        // Clicking a star locks in the rating and updates the hidden input
        star.addEventListener('click', () => {
            const value = index + 1; // stars are 1-5, index is 0-4
            document.querySelector('.star-rating-input').dataset.selected = value;
            document.querySelector('input[name="stars"]').value = value; // update the form field
            highlightStars(index);
        });
    });

    /**
     * highlightStars(upToIndex) — Fills stars up to a given index with ⭐
     * Stars after that index are shown as empty ☆
     */
    function highlightStars(upToIndex) {
        starInputs.forEach((star, i) => {
            star.textContent = i <= upToIndex ? '⭐' : '☆';
        });
    }
}
