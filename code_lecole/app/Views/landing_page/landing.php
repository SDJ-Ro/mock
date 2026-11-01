<!DOCTYPE html>
<html lang="<?= e(country('language')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(school('name')) ?> — <?= e(school('tagline')) ?></title>
    <link rel="icon" href="<?= e(school('logo')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/button.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/label.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/tag.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/landing.css')) ?>">
    <script src="<?= e(asset('js/reveal.js')) ?>" defer></script>
</head>
<body>

<?php require dirname(__DIR__) . '/components/icons.php'; ?>

<main class="l-hero">
    <img class="l-hero-photo" src="<?= e(school('campus_image')) ?>" alt="The <?= e(school('name')) ?> campus">
    <div class="l-hero-scrim" aria-hidden="true"></div>

    <div class="l-content">

        <!-- Header -->
        <header class="wrap l-header">
            <a class="l-brand" href="/" aria-label="<?= e(school('name')) ?> home">
                <img src="<?= e(school('logo')) ?>" alt="<?= e(school('name')) ?> logo">
                <span class="l-brand-name"><?= e(school('name')) ?></span>
            </a>

            <nav class="l-header-nav" aria-label="Main">
                <a class="l-header-link l-hide-on-phone" href="#about">About Us</a>

                <?php if (feature('honours_board')): ?>
                    <a class="l-header-link" href="/landing/achievements">Honours Board</a>
                <?php endif; ?>

                <a class="c-btn c-btn--accent" href="#community">Find Your Access</a>
            </nav>
        </header>

        <!-- Hero -->
        <section class="wrap l-hero-section">
            <div class="l-hero-text">
                <p class="c-label l-eyebrow l-fade-up"><?= icon('sparkles', 14) ?> Uncover your potential</p>

                <h1 class="l-hero-title l-fade-up l-delay-1"><?= e(school('tagline')) ?>.</h1>

                <p class="l-hero-desc l-fade-up l-delay-2">
                    Dive into a vibrant ecosystem of extracurriculars, arts, and athletics.
                    At <?= e(school('name')) ?>, we believe true growth happens when you explore beyond the classroom.
                </p>

                <div class="l-hero-actions l-fade-up l-delay-3">
                    <a class="c-btn c-btn--accent c-btn--large" href="#community">Join the Community <?= icon('arrowRight') ?></a>
                </div>
            </div>
        </section>

        <!-- Community -->
        <section id="community" class="l-section l-community">
            <div class="wrap">
                <div class="l-section-head">
                    <div>
                        <p class="c-label l-eyebrow">Made for the whole <?= e(strtolower(word('institution'))) ?></p>
                        <h2 class="l-h2">Every point of view belongs.</h2>
                    </div>
                    <p class="l-section-desc">
                        The <?= e(school('name')) ?> community stays close to learning without adding noise.
                        Each person sees what helps them participate with clarity, care, and confidence.
                    </p>
                </div>

                <div class="l-pathways">
                    <?php foreach ($pathways as $card): ?>
                        <article class="l-pathway-card tone-<?= e($card['tone']) ?>" data-reveal>
                            <img class="l-pathway-avatar" src="<?= e($card['photo']) ?>" alt="<?= e($card['alt']) ?>">
                            <h3 class="l-pathway-title"><?= e($card['title']) ?></h3>
                            <p class="l-pathway-copy"><?= e($card['text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="l-center-action">
                    <a class="c-btn c-btn--dark c-btn--pill c-btn--large" href="/auth">Sign In <?= icon('arrowRight') ?></a>
                </div>
            </div>
        </section>

        <!-- Achievements -->
        <section id="achievements" class="l-section l-achievements">
            <div class="wrap">
                <div class="l-section-head">
                    <div>
                        <p class="c-label l-eyebrow"><?= e(word('student')) ?> achievements</p>
                        <h2 class="l-h2">Won on the field, stage, and bench.</h2>
                    </div>
                    <p class="l-section-desc">
                        Every result is recorded with the same detail our <?= e(strtolower(word('teachers'))) ?> submit — tournament,
                        organiser, venue, level, and the <?= e(strtolower(word('students'))) ?> who were there.
                        Here are a few from the last two seasons.
                    </p>
                </div>

                <div class="ach-list">
                    <?php $showDetails = false; ?>
                    <?php foreach ($highlights as $i => $item): ?>
                        <?php require dirname(__DIR__) . '/components/_achievement_row.php'; ?>
                    <?php endforeach; ?>
                </div>


                <?php if (feature('honours_board')): ?>
                    <div class="l-center-action">
                        <a class="c-btn c-btn--dark c-btn--pill c-btn--large" href="/landing/achievements">See All Achievements <?= icon('arrowRight') ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- About -->
        <section id="about" class="l-section l-about">
            <div class="wrap">
                <div class="l-about-head">
                    <p class="c-label l-eyebrow">Our system</p>
                    <h2 class="l-h2">Focus on growth, we handle the rest.</h2>
                    <p class="l-section-desc">
                        <?= e(school('name')) ?> is built around the idea that extracurriculars are just as important as academics
                        for growth. Our platform brings both together, giving a complete picture of every <?= e(strtolower(word('student'))) ?>'s journey.
                    </p>
                </div>

                <div class="l-features">
                    <div class="l-feature-card">
                        <span class="l-feature-icon tone-lightblue"><?= icon('trendingUp', 24) ?></span>
                        <h3 class="l-feature-title">Extracurricular Growth</h3>
                        <p class="l-feature-copy">Manage clubs, teams, and creative projects alongside academics. Track participation, achievements, and personal development outside the classroom.</p>
                    </div>

                    <div class="l-feature-card">
                        <span class="l-feature-icon tone-tan"><?= icon('shieldCheck', 24) ?></span>
                        <h3 class="l-feature-title">Character Certificates</h3>
                        <p class="l-feature-copy">Generate certificates from achievements and participation. Recognise and reward the character traits that matter most.</p>
                    </div>

                    <div class="l-feature-card l-feature-card--wide">
                        <span class="l-feature-icon tone-sunshine"><?= icon('bookOpen', 24) ?></span>
                        <h3 class="l-feature-title">Academic Functions</h3>
                        <p class="l-feature-copy">Handle report cards, grading, and curriculum planning. Progress is tracked efficiently, giving <?= e(strtolower(word('teachers'))) ?> more time to teach.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="l-footer">
            <div class="wrap l-footer-grid">
                <div>
                    <div class="l-brand">
                        <img src="<?= e(school('logo')) ?>" alt="<?= e(school('name')) ?> logo">
                        <span class="l-brand-name"><?= e(school('name')) ?></span>
                    </div>
                    <p class="l-footer-tagline"><?= e(school('about')) ?></p>
                </div>

                <div class="l-footer-col">
                    <p class="c-label l-eyebrow"><?= e(word('institution')) ?></p>
                    <p class="l-footer-line"><?= implode('<br>', array_map('e', school('address'))) ?></p>
                    <a href="mailto:<?= e(school('contact_email')) ?>"><?= e(school('contact_email')) ?></a>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', school('phone'))) ?>"><?= e(school('phone')) ?></a>
                </div>

                <div class="l-footer-col">
                    <p class="c-label l-eyebrow">Portals</p>
                    <?php foreach (config('app.roles') as $role): ?>
                        <a href="/auth/<?= e($role) ?>"><?= e(word($role)) ?> Portal</a>
                    <?php endforeach; ?>
                </div>

                <div class="l-footer-col">
                    <p class="c-label l-eyebrow">About</p>
                    <a href="#about">Our System</a>
                    <a href="#about">Extracurriculars</a>
                    <a href="#about">Academics</a>
                </div>
            </div>

            <div class="l-footer-bottom">
                <div class="wrap l-footer-bottom-inner">
                    <p>© <?= date('Y') ?> <?= e(school('name')) ?></p>
                    <p><?= e(school('motto')) ?></p>
                </div>
            </div>
        </footer>

    </div>
</main>

</body>
</html>
