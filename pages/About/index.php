<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    header('Location: /pages/Login');
    exit;
}

// Page configuration
$pageTitle = 'About Us';
$pageCSS = '<link rel="stylesheet" href="/assets/css/about.css">';

$content = '
<div class="about-container">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-content">
            <h1 class="company-title">[COMPANY NAME]</h1>
            <p class="company-tagline">[Your innovative tagline here]</p>
        </div>
    </section>

    <!-- Company Description -->
    <section class="company-description">
        <div class="container">
            <h2>About Our Company</h2>
            <div class="description-content">
                <p class="lead-text">
                    [Insert compelling company description here. Talk about your mission, vision, and what makes your company unique in the industry.]
                </p>
                <p>
                    [Add more details about your company history, values, and achievements. This section should give visitors a clear understanding of who you are and what you stand for.]
                </p>
                <p>
                    [Include information about your products/services, your commitment to quality, and how you serve your customers.]
                </p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2>Meet Our Team</h2>
            <p class="team-intro">Our talented team of professionals dedicated to delivering excellence</p>
            
            <div class="team-grid">
                <!-- Team Member 1 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/assets/img/team/member1.jpg" alt="[Member 1 Name]" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">[Member 1 Name]</h3>
                        <p class="member-title">[Job Title]</p>
                        <p class="member-description">
                            [Brief description of team member 1, their role, experience, and what they bring to the company.]
                        </p>
                        <div class="member-contact">
                            <a href="mailto:[email]" class="contact-link">✉️ [email@company.com]</a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 2 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/assets/img/team/member2.jpg" alt="[Member 2 Name]" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">[Member 2 Name]</h3>
                        <p class="member-title">[Job Title]</p>
                        <p class="member-description">
                            [Brief description of team member 2, their role, experience, and what they bring to the company.]
                        </p>
                        <div class="member-contact">
                            <a href="mailto:[email]" class="contact-link">✉️ [email@company.com]</a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 3 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/assets/img/team/member3.jpg" alt="[Member 3 Name]" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">[Member 3 Name]</h3>
                        <p class="member-title">[Job Title]</p>
                        <p class="member-description">
                            [Brief description of team member 3, their role, experience, and what they bring to the company.]
                        </p>
                        <div class="member-contact">
                            <a href="mailto:[email]" class="contact-link">✉️ [email@company.com]</a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 4 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/assets/img/team/member4.jpg" alt="[Member 4 Name]" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">[Member 4 Name]</h3>
                        <p class="member-title">[Job Title]</p>
                        <p class="member-description">
                            [Brief description of team member 4, their role, experience, and what they bring to the company.]
                        </p>
                        <div class="member-contact">
                            <a href="mailto:[email]" class="contact-link">✉️ [email@company.com]</a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 5 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/assets/img/team/member5.jpg" alt="[Member 5 Name]" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">[Member 5 Name]</h3>
                        <p class="member-title">[Job Title]</p>
                        <p class="member-description">
                            [Brief description of team member 5, their role, experience, and what they bring to the company.]
                        </p>
                        <div class="member-contact">
                            <a href="mailto:[email]" class="contact-link">✉️ [email@company.com]</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
';

include LAYOUTS_PATH . '/main.layout.php';
?>