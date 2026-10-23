<?php
require_once 'includes/header.php';

$faqCategories = [
    [
        'title' => 'Hours & Scheduling',
        'icon' => 'bi-clock',
        'questions' => [
            [
                'question' => 'Are you open 7 days a week?',
                'answer' => 'Yes. We are open Monday through Saturday from 7:00 am to 10:00 pm, and Sunday from 10:00 am to 5:00 pm.',
            ],
            [
                'question' => 'What are your driving lesson hours?',
                'answer' => 'Driving lessons are available Monday through Sunday from 7:00 am to 9:00 pm. Please schedule your lesson in advance.',
            ],
            [
                'question' => 'Can I get a DMV road test date on Saturday?',
                'answer' => 'Yes. The DMV offers road tests on Saturdays.',
            ],
        ],
    ],
    [
        'title' => 'Pickup & Packages',
        'icon' => 'bi-geo-alt',
        'questions' => [
            [
                'question' => 'Do you pick up or drop off students?',
                'answer' => 'Yes. Free pickup and drop-off are available when your location is within a 10-minute drive of our office.',
            ],
            [
                'question' => 'Can I upgrade my package?',
                'answer' => 'Yes. You can upgrade your package. Contact us and we’ll help you choose an option.',
            ],
        ],
    ],
    [
        'title' => 'Lessons, Instructors & Payment',
        'icon' => 'bi-person-check',
        'questions' => [
            [
                'question' => 'How much is a 45-minute driving lesson?',
                'answer' => 'A single 45-minute driving lesson is $45. With a package, lessons are $40 each.',
            ],
            [
                'question' => 'Do you have female instructors?',
                'answer' => 'Yes. We have three female instructors.',
            ],
            [
                'question' => 'How can I pay?',
                'answer' => 'We offer payment plan options. Contact us to discuss a plan for your lessons or package.',
            ],
        ],
    ],
    [
        'title' => '5-Hour Pre-Licensing Course',
        'icon' => 'bi-mortarboard',
        'questions' => [
            [
                'question' => 'Do you offer the 5-hour course?',
                'answer' => 'Yes. We offer the 5-Hour Pre-Licensing Course every day.',
            ],
            [
                'question' => 'What do I need to attend the 5-hour course?',
                'answer' => 'Bring your DMV learner permit photo ID or DMV state ID, along with your paper learner permit.',
            ],
            [
                'question' => 'How does the 5-Hour Pre-Licensing Course (MV-278) work, and how long is it valid?',
                'answer' => 'The course is a mandatory prerequisite for scheduling a road test. Upon completion, you receive an MV-278 certificate, which is valid for one year.',
            ],
            [
                'question' => 'What documents must I present to my instructor on my first driving lesson?',
                'answer' => 'Bring a valid New York State learner permit (Class D or DJ) or an interim paper permit issued by the DMV before your lesson. Digital photos of permits cannot be accepted.',
            ],
        ],
    ],
    [
        'title' => 'Road Tests & Vehicles',
        'icon' => 'bi-car-front',
        'questions' => [
            [
                'question' => 'What model car do you provide?',
                'answer' => 'We provide a 2020 Toyota Corolla LE.',
            ],
            [
                'question' => 'How much does road test car rental cost?',
                'answer' => 'Car rental costs $100–$150, depending on the road test location.',
            ],
            [
                'question' => 'How do I get to my road test site?',
                'answer' => 'You can travel to the road test location with our instructor and car.',
            ],
            [
                'question' => 'Will the DMV provide a car for my road test?',
                'answer' => 'No. The DMV does not provide a road test car. The driving school can provide one for your test.',
            ],
            [
                'question' => 'Can I use a friend or family member’s car for my road test?',
                'answer' => 'You may provide your own vehicle, but it must meet DMV requirements, including legal window tint, no dashboard warning lights, and a valid New York State inspection sticker. A school vehicle is another option.',
            ],
        ],
    ],
    [
        'title' => 'Fees & DMV',
        'icon' => 'bi-receipt',
        'questions' => [
            [
                'question' => 'What is the DMV skills test fee?',
                'answer' => 'The DMV skills test fee is $10.',
            ],
        ],
    ],
];
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<section class="faq-hero py-5 text-center">
    <div class="container py-4">
        <span class="badge bg-white text-primary border rounded-pill px-3 py-2 mb-3">
            <i class="bi bi-chat-square-text me-1" aria-hidden="true"></i> SK Driving School • Help Center
        </span>
        <h1 class="display-4 fw-bold mb-3">Frequently Asked Questions</h1>
        <p class="lead text-muted mb-0 mx-auto">Quick answers about lessons, packages, road tests, and getting started.</p>
        <div class="faq-count mx-auto mt-4">
            <i class="bi bi-patch-question me-2 text-primary" aria-hidden="true"></i><?= array_sum(array_map(static fn($category) => count($category['questions']), $faqCategories)) ?> answers to help you get on the road
        </div>
    </div>
</section>

<main class="faq-section py-5">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($faqCategories as $categoryIndex => $category): ?>
                <?php $accordionId = 'faq-category-' . $categoryIndex; ?>
                <div class="col-lg-6">
                    <section class="faq-category h-100" aria-labelledby="<?= $accordionId ?>-heading">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="faq-category-icon"><i class="bi <?= htmlspecialchars($category['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                            <h2 class="h5 fw-bold mb-0" id="<?= $accordionId ?>-heading"><?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        </div>
                        <div class="accordion faq-accordion" id="<?= $accordionId ?>">
                            <?php foreach ($category['questions'] as $questionIndex => $item): ?>
                                <?php $collapseId = $accordionId . '-question-' . $questionIndex; ?>
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                            <?= htmlspecialchars($item['question'], ENT_QUOTES, 'UTF-8') ?>
                                        </button>
                                    </h3>
                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse" data-bs-parent="#<?= $accordionId ?>">
                                        <div class="accordion-body text-muted">
                                            <?= htmlspecialchars($item['answer'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
