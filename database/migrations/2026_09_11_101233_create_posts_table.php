<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // 'jobindex' | 'jobnet'
            $table->string('source_id'); // tid | jobAdId
            $table->string('title');
            $table->longText('description')->nullable(); // HTML

            // company (denormalized)
            $table->string('company_name');
            $table->string('cvr', 10)->nullable();
            $table->string('company_logo_url')->nullable();
            $table->string('company_website')->nullable();

            // location (primary address)
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode', 10)->nullable();
            $table->string('country')->default('Danmark');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // urls
            $table->string('canonical_url')->nullable();

            // classification
            $table->string('occupation')->nullable();

            // flags
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_part_time')->default(false);
            $table->boolean('deadline_is_asap')->default(false);
            $table->boolean('is_archived')->default(false);

            // dates
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('deadline_at')->nullable()->index();

            $table->json('raw')->nullable();
            $table->timestamps();

            $table->unique(['source', 'source_id']);
            $table->index(['source', 'published_at']);
            $table->index('company_name');
            // $table->fullText(['title', 'description']); // MySQL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

/* -------------------------------------------------------------------------- */
/*                                  Jobindex                                  */
/* -------------------------------------------------------------------------- */
//   0 => array:29 [▼
//     "addresses" => array:1 [▼
//       0 => array:6 [▼
//         "city" => "Lystrup"
//         "coordinates" => array:2 [▼
//           "latitude" => 56.22617216
//           "longitude" => 10.24599834
//         ]
//         "id" => 4339521
//         "line" => "Lægårdsvej 22B"
//         "simple_string" => "Lægårdsvej 22B, 8520 Lystrup"
//         "zipcode" => "8520"
//       ]
//     ]
//     "app_apply_url" => null
//     "apply_deadline" => "2026-11-04T23:00:00Z"
//     "apply_deadline_asap" => false
//     "apply_url" => null
//     "area" => "8520 Lystrup"
//     "bottom_logo" => null
//     "company" => array:15 [▶]
//     "companytext" => "ONLIMITED ApS"
//     "distance" => 55.289944
//     "firstdate" => "2026-09-10"
//     "geoareaid" => array:11 [▶]
//     "geojson" => array:2 [▶]
//     "has_spo" => false
//     "headline" => "Tech lead"
//     "home_workplace" => false
//     "is_archived" => false
//     "is_local" => true
//     "lastdate" => "2026-11-05"
//     "listlogo_url" => null
//     "quickapply_clientid" => null
//     "rating" => array:2 [▶]
//     "share_url" => "https://www.jobindex.dk/vis-job/r13990520"
//     "source" => null
//     "tid" => "r13990520"
//     "top_logo" => null
//     "url" => "https://www.jobindex.dk/c?t=r13990520&ctx=w&jobsearch_position=1"
//     "video" => null
//     "workplace_company" => array:15 [▼
//       "bottom_logo_url" => null
//       "bundurlisjoburl" => false
//       "companyprofile_url" => "https://www.jobindex.dk/virksomhed/61364/onlimited-aps#om-virksomhed"
//       "editurl" => "/adm/company/edit/61364"
//       "employment_place" => []
//       "get_logo_company" => "/adm/media/company/61364"
//       "homeurl" => "https://onlimited.dk/"
//       "humanjob_workplace" => false
//       "id" => 61364
//       "logo" => "https://www.jobindex.dk/img/logo/Onlimited_logo.png"
//       "name" => "Onlimited ApS"
//       "num_followers" => 3
//       "rating_url" => "https://www.jobindex.dk/virksomhed/61364/onlimited-aps#evalueringer"
//       "use_joburl_pa" => false
//       "video_logo_url" => null
//     ]
//   ]

/* -------------------------------------------------------------------------- */
/*                                   Jobnet                                   */
/* -------------------------------------------------------------------------- */
// 0 => array:21 [▼
//   "country" => "Danmark"
//   "hiringOrgName" => "Eupry"
//   "occupation" => "Softwareudvikler, backend"
//   "conceptUriDa" => "http://data.star.dk/esco/occupation/2cd16234-7821-4d6b-b90e-aa4a629b3fe3"
//   "jobAnnouncementTypeName" => "Almindelige vilkår"
//   "workHourPartTime" => false
//   "jobAdId" => "fd60f289-4bca-42ed-832a-0f225f40a48e"
//   "jobAdUrl" => "https://apply.workable.com/eupry-aps/j/FB57A68D91/"
//   "hasLogo" => false
//   "workPlaceAddress" => "   "
//   "cvr" => "30536665"
//   "title" => "Senior Full-Stack Engineer (PHP/Laravel)"
//   "description" => """
//     <p><strong>About Eupry</strong><br>
//     Eupry is a fast-growing scale-up revolutionizing the compliance industry with our innovative service offerings. With over 20,000 IoT devices deployed worldwide, ▶
//     <p>At Eupry, you’ll work in a collaborative engineering environment where technical excellence, clear standards, and effective teamwork drive our success. Our p ▶
//     <p><strong>As a Senior Full-Stack Engineer at Eupry, you will</strong></p>
//     <ul>
//     <li><p>Master the Full Stack: You will take ownership of the full lifecycle of our applications, bridging backend (Laravel) and frontend (Vue.js) development to ▶
//     </li>
//     <li><p>Set the right standards: Establish development principles, coding guidelines, and architectural practices that ensure long-term system quality and effect ▶
//     </li>
//     <li><p>Make confident decisions: Take ownership of architectural and technical direction, supporting Eupry’s growth and ability to deliver effectively.</p>
//     </li>
//     <li><p>Deliver roadmap items: Collaborate with product managers and stakeholders to design and implement features aligned with our product strategy.</p>
//     </li>
//     <li><p>Strengthen technical foundations: Reduce technical debt, improve workflows, and set high standards for clean, testable, and maintainable code.</p>
//     </li>
//     <li><p>Optimize performance &amp; integrations: Improve system efficiency and reliability while ensuring smooth integrations with third-party APIs and services. ▶
//     </li>
//     </ul>
//     <p><strong>Requirements</strong></p>
//     <p>We’re seeking a senior engineer who combines strong technical skills with a sense of responsibility for standards, alignment, and effectiveness.</p>
//     <p><strong>Experience and mindset</strong></p>
//     <ul>
//     <li><p>Strong experience in PHP development, with a focus on Laravel.</p>
//     </li>
//     <li><p>Proven background in application architecture and system design, with confidence in making high-impact technical decisions.</p>
//     </li>
//     <li><p>Experience in setting coding standards, guidelines, or best practices in a professional development environment.</p>
//     </li>
//     <li><p>Experience in coordinating and aligning technical decisions across multiple teams or pods.</p>
//     </li>
//     <li><p>Solid understanding of MySQL, with the ability to investigate data issues and design improvements.</p>
//     </li>
//     <li><p>Background in scaling customer-facing applications is highly desirable.</p>
//     </li>
//     <li><p>Ownership mindset. Committed to improving effectiveness and driving initiatives from concept to delivery.</p>
//     </li>
//     <li><p>Experience with AI tools and workflows, as we prioritize integrating AI into our way of working.</p>
//     </li>
//     </ul>
//     <p><strong>Core Technical Skills</strong></p>
//     <ul>
//     <li><p>Expertise in Laravel’s advanced features (e.g., Eloquent, queues, event broadcasting).</p>
//     </li>
//     <li><p>Strong database design and query optimization skills.</p>
//     </li>
//     <li><p>Familiarity with front-end frameworks (e.g., Vue.js) is a plus.</p>
//     </li>
//     <li><p>Experience with third-party API integrations.</p>
//     </li>
//     <li><p>Solid understanding of Git and modern development workflows.</p>
//     </li>
//     </ul>
//     <p><strong>Nice-to-have</strong></p>
//     <ul>
//     <li><p>Experience with DevOps practices (CI/CD, Docker, cloud infrastructure) is a plus, helping us improve our deployment and scalability.</p>
//     </li>
//     <li><p>Good-to-have knowledge of Redis, SQS, and queuing techniques, as these are central to our alarm system.</p>
//     </li>
//     </ul>
//     <p><strong>Soft skills</strong></p>
//     <ul>
//     <li><p>Confident in decision-making and able to set direction for others to follow.</p>
//     </li>
//     <li><p>Strong communication skills, able to align technical and non-technical stakeholders.</p>
//     </li>
//     <li><p>Collaborative mindset, thriving in a professional, cross-functional environment.</p>
//     </li>
//     <li><p>Organized, mindful and detail-oriented, with a proactive and solution-focused approach.</p>
//     </li>
//     </ul>
//     <p><strong>Opportunity</strong></p>
//     <ul>
//     <li><p>A central role in shaping the standards, architecture, and effectiveness of Eupry’s customer-facing applications.</p>
//     </li>
//     <li><p>Responsibility for aligning technical practices across the organization, ensuring efficiency and scalability.</p>
//     </li>
//     <li><p>A professional engineering culture where your decisions and standards directly support Eupry’s growth journey.</p>
//     </li>
//     </ul>
//     <p><strong>Benefits</strong></p>
//     <ul>
//     <li><p>Monthly Friday bar to connect with colleagues.</p>
//     </li>
//     <li><p>A yearly offsite event to align, collaborate, and celebrate together.</p>
//     </li>
//     <li><p>Opportunities for professional development and career growth.</p>
//     </li>
//     </ul>
//     """
//   "applicationDeadline" => "2026-11-16T00:00:00+01:00"
//   "applicationDeadlineSort" => "2026-11-16T00:00:00+01:00"
//   "applicationDeadlineStatus" => "ExpirationDate"
//   "isSeen" => false
//   "isFavorite" => false
//   "isJoblogged" => false
//   "isExternal" => true
//   "publicationDate" => "2026-08-16T00:00:00+02:00"
// ]