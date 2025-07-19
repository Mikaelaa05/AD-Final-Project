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
$pageCSS = '<link rel="stylesheet" href="/assets/css/about.css?v=' . time() . '">';

$content = '
<div class="about-container">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-content">
            <h1 class="company-title"><span class="red-text">SIN</span>THESIZE Corp.</h1>
            <p class="company-tagline">We don\'t write tragedies, we write SINS.</p>
        </div>
    </section>

    <!-- Company Description -->
    <section class="company-description">
        <div class="container">
            <h2>ABOUT OUR COMPANY</h2>
            <div class="description-content">
                <p class="lead-text">
                    At SINTHESIZE Corp., we don\'t follow conventional paths. We forge new realities through cutting-edge technology and uncompromising innovation. Where others see limitations, we see opportunities to transcend boundaries.
                </p>
                <p>
                    Founded on the principle that true innovation requires breaking the rules, SINTHESIZE specializes in developing revolutionary digital solutions that challenge the status quo. Our team of elite developers, designers, and visionaries work in the shadows of the tech world, crafting experiences that blur the line between reality and digital artistry.
                </p>
                <p>
                    We believe in the power of controlled chaos, turning complex problems into elegant solutions, transforming ordinary projects into extraordinary digital symphonies. Every line of code we write, every interface we design, carries the mark of our relentless pursuit of perfection in an imperfect world.
                </p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2>MEET OUR TEAM</h2>
            <p class="team-intro">Our talented team of professionals dedicated to delivering excellence</p>

            <div class="team-grid">
                <!-- Team Member 1 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/pages/About/assets/img/Boris.jpg" alt="Boris Dela Cruz" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Boris Dela Cruz</h3>
                        <p class="member-title">Database Manager</p>
                        <p class="member-description">
                            Expert database architect responsible for designing and maintaining our database. Boris ensures optimal performance and security across all database systems.
                        </p>
                        <div class="member-contact">
                            <p class="contact-email">✉️ borisdc@email.com</p>
                        </div>
                    </div>
                </div>

                <!-- Team Member 2 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/pages/About/assets/img/Mika.jpg" alt="Mikaela Andrea Cid" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Mikaela Andrea Cid</h3>
                        <p class="member-title">Quality Assurance Manager</p>
                        <p class="member-description">
                            Lead QA specialist ensuring product excellence through comprehensive testing and quality control. Mikaela maintains our high standards across all deliverables.
                        </p>
                        <div class="member-contact">
                            <p class="contact-email">✉️ mikaela.cid@gmail.com</p>
                        </div>
                    </div>
                </div>

                <!-- Team Member 3 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/pages/About/assets/img/JM.jpg" alt="Jan-Michael II Laguesma" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Jan-Michael II Laguesma</h3>
                        <p class="member-title">Back-end Developer</p>
                        <p class="member-description">
                            Skilled backend engineer developing robust server-side solutions and APIs. Jan-Michael builds the core functionality that powers our applications.
                        </p>
                        <div class="member-contact">
                            <p class="contact-email">✉️ micjolaguesma@gmail.com</p>
                        </div>
                    </div>
                </div>

                <!-- Team Member 4 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/pages/About/assets/img/Jam.jpg" alt="Baron Jamille Andres" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Baron Jamille Andres</h3>
                        <p class="member-title">Designer</p>
                        <p class="member-description">
                            Creative designer crafting intuitive user experiences and stunning visual interfaces. Jamille brings artistic vision and user-centered design to every project.
                        </p>
                        <div class="member-contact">
                            <p class="contact-email">✉️ mung.andres@gmail.com</p>
                        </div>
                    </div>
                </div>

                <!-- Team Member 5 -->
                <div class="team-member">
                    <div class="member-photo">
                        <img src="/pages/About/assets/img/Castro.JPG" alt="Syrrlian Castro" onerror="this.style.display=\'none\'" />
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Syrrlian Castro</h3>
                        <p class="member-title">Front-End Developer</p>
                        <p class="member-description">
                            Expert front-end developer creating responsive and interactive user interfaces. Syrrlian transforms designs into seamless web experiences.
                        </p>
                        <div class="member-contact">
                            <p class="contact-email">✉️ punzubzero@email.com</p>
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