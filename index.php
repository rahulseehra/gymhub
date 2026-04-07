<?php
/**
 * GymHub - Homepage
 * index.php
 *
 * This is the main landing page of the GymHub website.
 * It is the first page visitors see when they arrive at the site.
 *
 * What this page shows:
 *   - Hero section: full-height banner with headline, stats, and CTA buttons
 *   - Category quick links: clickable icons for each class type
 *   - Featured classes: top 6 classes pulled from the database, sorted by rating
 *   - Promo video section: background image with a play button for the lightbox video
 *   - Membership tiers: all 3 plans with features and prices from the database
 *   - Instructor spotlight: top 3 instructors pulled from the database
 *   - Testimonials: recent positive ratings pulled from the database
 *   - Call to action banner: encourages visitors to join or book a class
 *
 * All content sections pull live data from MySQL using PDO prepared statements.
 * The page is fully responsive and works on desktop and mobile.
 */

// ── SEO meta tags ─────────────────────────────────────────────────────────────
// These are picked up by header.php when it builds the <head> section.
// Writing good title and description text helps Google index the page properly.
$page_title       = 'GymHub — Train Harder. Live Better.';
$page_description = 'GymHub is Windsor\'s premier fitness community. Book HIIT, yoga, boxing, CrossFit and 20+ classes online. Join today and transform your fitness journey.';
$page_keywords    = 'gym Windsor, fitness classes, HIIT, yoga, CrossFit, boxing, personal training, gym membership Windsor Ontario';
$active_nav       = 'home'; // highlights the "Home" nav link

// ── Load dependencies ─────────────────────────────────────────────────────────
// db.php gives us the $pdo database connection and helper functions.
// header.php outputs the <head>, announcement bar, and navigation.
require_once 'config/db.php';
require_once 'includes/header.php';

// ── Database queries ──────────────────────────────────────────────────────────
// We run all our queries before outputting any HTML.
// This is good practice — get your data first, then display it.

// Get the top 6 classes sorted by instructor rating (best first)
// We LEFT JOIN instructors and users to get the instructor's name
$stmt = $pdo->query("
    SELECT c.*, i.rating,
           CONCAT(u.first_name, ' ', u.last_name) AS instructor_name
    FROM classes c
    LEFT JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN users u       ON i.user_id = u.id
    WHERE c.status = 'active'
    ORDER BY i.rating DESC
    LIMIT 6
");
$featured_classes = $stmt->fetchAll();

// Get the top 3 instructors for the spotlight section
$stmt = $pdo->query("
    SELECT i.*, CONCAT(u.first_name, ' ', u.last_name) AS name
    FROM instructors i
    JOIN users u ON i.user_id = u.id
    ORDER BY i.rating DESC
    LIMIT 3
");
$instructors = $stmt->fetchAll();

// Get 3 recent 4-or-5-star reviews to use as testimonials
// We join users to get the member's name and classes to get the class name
$stmt = $pdo->query("
    SELECT r.stars, r.comment,
           CONCAT(u.first_name, ' ', u.last_name) AS member_name,
           c.title AS class_name
    FROM ratings r
    JOIN users u   ON r.user_id  = u.id
    JOIN classes c ON r.class_id = c.id
    WHERE r.stars >= 4
    ORDER BY r.created_at DESC
    LIMIT 3
");
$testimonials = $stmt->fetchAll();

// Get overall site stats for the hero section counter display
$total_classes     = $pdo->query("SELECT COUNT(*) FROM classes WHERE status='active'")->fetchColumn();
$total_members     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn();
$total_instructors = $pdo->query("SELECT COUNT(*) FROM instructors")->fetchColumn();
$total_locations   = $pdo->query("SELECT COUNT(*) FROM locations")->fetchColumn();
?>

<!-- ════════════════════════════════════════════════════════════════════
     HERO SECTION
     Full-height banner with background image, dark overlay, and content.
     The stats animate from 0 to their real value using JS counters.
     data-target tells JavaScript what number to count up to.
     ════════════════════════════════════════════════════════════════════ -->
<section class="hero" aria-label="Hero banner">

    <!-- Background image with dark gradient overlay -->
    <div class="hero-bg">
        <div class="hero-overlay"></div>
        <img src="public/assets/images/hero-bg.jpg"
             alt="GymHub gym floor with members training"
             class="hero-image">
    </div>

    <!-- Text content over the image -->
    <div class="hero-content">
        <div class="hero-badge">🏆 Windsor's #1 Fitness Community</div>
        <h1 class="hero-title">
            Train<br>
            <span class="hero-accent">Harder.</span><br>
            Live Better.
        </h1>
        <p class="hero-subtitle">
            Over <?= $total_classes ?>+ world-class classes. Elite instructors.
            Real results. Your transformation starts today.
        </p>

        <!-- Two call-to-action buttons -->
        <div class="hero-actions">
            <a href="pages/classes.php" class="btn btn-primary btn-lg">
                Browse Classes
            </a>
            <a href="pages/membership.php" class="btn btn-outline btn-lg">
                View Membership
            </a>
        </div>

        <!-- Stats strip — numbers animate up on scroll using main.js -->
        <div class="hero-stats">
            <div class="hero-stat">
                <!-- data-target is read by the JS counter animation -->
                <strong class="counter" data-target="<?= $total_members ?>"><?= $total_members ?></strong>
                <span>Members</span>
            </div>
            <div class="hero-stat">
                <strong class="counter" data-target="<?= $total_classes ?>"><?= $total_classes ?></strong>
                <span>Classes</span>
            </div>
            <div class="hero-stat">
                <strong class="counter" data-target="<?= $total_instructors ?>"><?= $total_instructors ?></strong>
                <span>Instructors</span>
            </div>
            <div class="hero-stat">
                <strong class="counter" data-target="<?= $total_locations ?>"><?= $total_locations ?></strong>
                <span>Locations</span>
            </div>
        </div>
    </div>

    <!-- Small animated scroll indicator arrow at the bottom -->
    <div class="hero-scroll" aria-hidden="true">
        <span>Scroll</span>
        <div class="scroll-arrow"></div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════
     CATEGORY QUICK LINKS
     Six clickable icons that filter the classes page by category.
     Each link passes a category query parameter to classes.php.
     ════════════════════════════════════════════════════════════════════ -->
<section class="categories section-pad" aria-label="Class categories">
    <div class="container">
        <div class="category-grid">
            <?php
            // Define the categories, their icons, and URL slugs
            // To add a new category, just add another array here
            $categories = [
                ['Strength',  '🏋️', 'strength'],
                ['Cardio',    '🏃', 'cardio'],
                ['Yoga',      '🧘', 'yoga'],
                ['Combat',    '🥊', 'combat'],
                ['Wellness',  '🌿', 'wellness'],
                ['Kids',      '⭐', 'kids'],
            ];
            foreach ($categories as [$label, $icon, $slug]):
            ?>
            <a href="pages/classes.php?category=<?= $slug ?>" class="category-card">
                <span class="category-icon"><?= $icon ?></span>
                <span class="category-label"><?= $label ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════
     FEATURED CLASSES
     Shows the top 6 classes sorted by instructor rating.
     Data comes from the classes + instructors + users tables.
     Each card links to the class detail and booking page.
     ════════════════════════════════════════════════════════════════════ -->
<section class="featured-classes section-pad" aria-label="Featured classes">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">What We Offer</span>
            <h2 class="section-title">Featured Classes</h2>
            <p class="section-subtitle">
                From high-intensity cardio to mindful yoga — there's something for every body.
            </p>
        </div>

        <div class="classes-grid">
            <?php foreach ($featured_classes as $class): ?>
            <article class="class-card">
                <div class="class-card-image">
                    <!-- onerror fallback shows a default image if the specific one is missing -->
                    <img src="public/assets/images/classes/<?= h($class['image']) ?>"
                         alt="<?= h($class['title']) ?> class at GymHub"
                         loading="lazy"
                         onerror="this.src='public/assets/images/classes/default-class.jpg'">
                    <span class="class-category-badge"><?= h($class['category']) ?></span>
                    <span class="class-price-badge">$<?= number_format($class['price'], 2) ?></span>
                </div>
                <div class="class-card-body">
                    <h3 class="class-title"><?= h($class['title']) ?></h3>
                    <!-- substr() truncates the description to 100 chars to keep cards uniform -->
                    <p class="class-description">
                        <?= h(substr($class['description'], 0, 100)) ?>...
                    </p>
                    <div class="class-meta">
                        <span>⏱ <?= h($class['duration_min']) ?> min</span>
                        <span>👤 <?= h($class['instructor_name'] ?? 'TBA') ?></span>
                        <?php if ($class['rating']): ?>
                        <span>⭐ <?= number_format($class['rating'], 1) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Show the two option chips — e.g. "Beginner" and "Advanced" -->
                    <div class="class-options">
                        <span class="option-chip"><?= h($class['option_a']) ?></span>
                        <span class="option-chip"><?= h($class['option_b']) ?></span>
                    </div>
                    <a href="pages/class-detail.php?id=<?= $class['id'] ?>" class="btn btn-secondary btn-sm">
                        View & Book
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Link to see all classes -->
        <div class="section-cta">
            <a href="pages/classes.php" class="btn btn-primary">See All <?= $total_classes ?> Classes</a>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════
     PROMO VIDEO SECTION
     Dark background image with overlay text and a play button.
     Clicking the button opens the video in a lightbox popup.
     The lightbox HTML is hidden by default (hidden attribute).
     JavaScript in main.js handles showing/hiding it.
     ════════════════════════════════════════════════════════════════════ -->
<section class="video-section" aria-label="GymHub promo video">
    <div class="video-overlay-text">
        <span class="section-tag">See It In Action</span>
        <h2>Experience GymHub</h2>
        <p>Take a virtual tour of our world-class facilities and see our community in action.</p>
        <!-- data-video attribute tells JS which video file to play -->
        <button class="btn btn-primary play-video-btn"
                data-video="public/assets/videos/gymhub-promo.mp4"
                aria-label="Play GymHub promo video">
            ▶ Watch the Video
        </button>
    </div>

    <!-- Darkened background image -->
    <div class="video-bg-image">
        <img src="public/assets/images/video-thumbnail.jpg" alt="GymHub facility" loading="lazy">
    </div>

    <!-- Video lightbox popup — hidden until play button is clicked -->
    <!-- JS removes the 'hidden' attribute to show this overlay -->
    <div class="video-lightbox" id="video-lightbox" role="dialog"
         aria-modal="true" aria-label="Video player" hidden>
        <div class="video-lightbox-inner">
            <!-- Close button — JS listens for clicks on this -->
            <button class="video-close-btn" id="video-close-btn" aria-label="Close video">✕</button>
            <video id="promo-video" controls width="100%">
                <source src="public/assets/videos/gymhub-promo.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════
     MEMBERSHIP TIERS
     Reads all active membership plans from the database.
     The features column stores a JSON array which we decode with
     json_decode() to get a PHP array we can loop through.
     The middle card (index 1 = Pro) gets the "popular" highlight styling.
     ════════════════════════════════════════════════════════════════════ -->
<section class="membership-preview section-pad" aria-label="Membership plans">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Choose Your Plan</span>
            <h2 class="section-title">Membership Plans</h2>
            <p class="section-subtitle">Flexible plans for every fitness goal and budget.</p>
        </div>

        <div class="membership-grid">
            <?php
            // Get all active memberships ordered cheapest first
            $memberships = $pdo->query("SELECT * FROM memberships WHERE status='active' ORDER BY price ASC")->fetchAll();

            // Emoji icons to show for each tier
            $tier_icons = ['Basic' => '🥉', 'Pro' => '🥈', 'Elite' => '🥇'];

            foreach ($memberships as $i => $plan):
                // Decode the JSON features array stored in the database
                $features = json_decode($plan['features'], true) ?? [];

                // The middle card (index 1) gets the popular highlight
                $is_popular = ($i === 1);
            ?>
            <div class="membership-card <?= $is_popular ? 'membership-card--popular' : '' ?>">
                <?php if ($is_popular): ?>
                <div class="popular-badge">Most Popular</div>
                <?php endif; ?>

                <div class="membership-icon"><?= $tier_icons[$plan['name']] ?? '⭐' ?></div>
                <h3 class="membership-name"><?= h($plan['name']) ?></h3>

                <div class="membership-price">
                    <span class="price-amount">$<?= number_format($plan['price'], 2) ?></span>
                    <span class="price-period">/month</span>
                </div>

                <!-- Loop through the features array from the database -->
                <ul class="membership-features">
                    <?php foreach ($features as $feature): ?>
                    <li>✓ <?= h($feature) ?></li>
                    <?php endforeach; ?>
                </ul>

                <!-- Primary style for popular card, secondary for others -->
                <a href="pages/membership.php" class="btn <?= $is_popular ? 'btn-primary' : 'btn-secondary' ?>">
                    Get Started
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════
     INSTRUCTOR SPOTLIGHT
     Shows the top 3 instructors sorted by rating.
     Data comes from the instructors table joined with users.
     ════════════════════════════════════════════════════════════════════ -->
<section class="instructors-preview section-pad" aria-label="Featured instructors">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Meet The Team</span>
            <h2 class="section-title">Elite Instructors</h2>
            <p class="section-subtitle">Learn from certified professionals who are passionate about your progress.</p>
        </div>

        <div class="instructors-grid">
            <?php foreach ($instructors as $instructor): ?>
            <div class="instructor-card">
                <div class="instructor-photo">
                    <!-- onerror shows the default instructor photo if theirs is missing -->
                    <img src="public/assets/images/instructors/<?= h($instructor['photo']) ?>"
                         alt="<?= h($instructor['name']) ?> — GymHub instructor"
                         loading="lazy"
                         onerror="this.src='public/assets/images/instructors/default-instructor.png'">
                </div>
                <div class="instructor-info">
                    <h3><?= h($instructor['name']) ?></h3>
                    <p class="instructor-specialties"><?= h($instructor['specialties']) ?></p>
                    <div class="instructor-rating">
                        ⭐ <?= number_format($instructor['rating'], 2) ?> / 5.00
                    </div>
                    <!-- substr truncates long bios to 120 characters -->
                    <p class="instructor-bio">
                        <?= h(substr($instructor['bio'], 0, 120)) ?>...
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-cta">
            <a href="pages/instructors.php" class="btn btn-secondary">Meet All Instructors</a>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════
     TESTIMONIALS
     Shows recent 4 and 5 star reviews as member quotes.
     Only displayed if there are any reviews in the database.
     ════════════════════════════════════════════════════════════════════ -->
<?php if (!empty($testimonials)): ?>
<section class="testimonials section-pad" aria-label="Member testimonials">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Real Results</span>
            <h2 class="section-title">What Our Members Say</h2>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($testimonials as $t): ?>
            <blockquote class="testimonial-card">
                <!-- str_repeat outputs star emojis equal to the rating number -->
                <div class="testimonial-stars">
                    <?= str_repeat('⭐', (int)$t['stars']) ?>
                </div>
                <p class="testimonial-text">"<?= h($t['comment']) ?>"</p>
                <footer class="testimonial-author">
                    <strong><?= h($t['member_name']) ?></strong>
                    <span><?= h($t['class_name']) ?> member</span>
                </footer>
            </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════════════
     CALL TO ACTION BANNER
     The bold orange strip near the bottom encouraging sign-ups.
     Shows different buttons depending on whether user is logged in.
     is_logged_in() is defined in config/db.php.
     ════════════════════════════════════════════════════════════════════ -->
<section class="cta-banner" aria-label="Join GymHub call to action">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Start Your Journey?</h2>
            <p>Join thousands of members who have transformed their lives at GymHub.</p>
            <div class="cta-actions">
                <?php if (!is_logged_in()): ?>
                <!-- Show join and browse buttons for guests -->
                <a href="pages/register.php" class="btn btn-primary btn-lg">Join For Free</a>
                <a href="pages/classes.php"  class="btn btn-outline btn-lg">Browse Classes</a>
                <?php else: ?>
                <!-- Show book and track buttons for logged-in members -->
                <a href="pages/classes.php"  class="btn btn-primary btn-lg">Book a Class</a>
                <a href="pages/progress.php" class="btn btn-outline btn-lg">Track Progress</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Load the footer — this closes <main>, renders the footer HTML,
// loads main.js, and closes </body> and </html>
require_once 'includes/footer.php';
?>
