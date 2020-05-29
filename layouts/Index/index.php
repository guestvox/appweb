<?php

defined('_EXEC') or die;

$this->dependencies->add(['css', '{$path.css}Index/index.css']);
$this->dependencies->add(['js', '{$path.js}Index/index.js']);

?>

<main class="landing_page_index">
    <section class="stl_1">
        <figure>
            <img src="{$path.images}index/stl_1_image_1.png" alt="Background">
        </figure>
        <div>
            <figure>
                <img src="{$path.images}logotype_white.png" alt="Guestvox">
            </figure>
            <h1>{$lang.landing_page_index_stl_1_text_1}</h1>
            <p>{$lang.landing_page_index_stl_1_text_2}</p>
            <a href="/login">{$lang.login}</a>
        </div>
    </section>
    <section class="stl_2">
        <figure>
            <img src="{$path.images}index/stl_2_image_1.png" alt="Background">
        </figure>
        <div>
            <h2>{$lang.landing_page_index_stl_2_text_1}</h2>
            <div>
                <div>
                    <i class="fas fa-desktop"></i>
                    <p>{$lang.landing_page_index_stl_2_text_2}</p>
                </div>
                <div>
                    <i class="fas fa-users"></i>
                    <p>{$lang.landing_page_index_stl_2_text_3}</p>
                </div>
                <div>
                    <i class="fas fa-chart-pie"></i>
                    <p>{$lang.landing_page_index_stl_2_text_4}</p>
                </div>
            </div>
            <a href="/operation">{$lang.know_more}</a>
        </div>
    </section>
    <section class="stl_3">
        <figure>
            <img src="{$path.images}index/stl_3_image_1.png" alt="Background">
        </figure>
        <div>
            <h2>{$lang.landing_page_index_stl_3_text_1}</h2>
            <div>
                <div>
                    <i class="fas fa-clipboard-list"></i>
                    <p>{$lang.landing_page_index_stl_3_text_2}</p>
                </div>
                <div>
                    <i class="fas fa-comments"></i>
                    <p>{$lang.landing_page_index_stl_3_text_3}</p>
                </div>
                <div>
                    <i class="fas fa-chart-bar"></i>
                    <p>{$lang.landing_page_index_stl_3_text_4}</p>
                </div>
            </div>
            <a href="/reputation">{$lang.know_more}</a>
        </div>
    </section>
    <section class="stl_4">
        <div>
            <i class="fas fa-bed"></i>
            <p>{$lang.landing_page_index_stl_4_text_1}</p>
        </div>
        <div>
            <i class="fas fa-utensils"></i>
            <p>{$lang.landing_page_index_stl_4_text_2}</p>
        </div>
        <div>
            <i class="fas fa-stethoscope"></i>
            <p>{$lang.landing_page_index_stl_4_text_3}</p>
        </div>
        <div>
            <i class="fas fa-users"></i>
            <p>{$lang.landing_page_index_stl_4_text_4}</p>
        </div>
    </section>
    <section class="stl_5">
        <div>
            <i class="fas fa-tablet-alt"></i>
            <p>{$lang.landing_page_index_stl_5_text_1}</p>
        </div>
        <div>
            <i class="fas fa-cloud"></i>
            <p>{$lang.landing_page_index_stl_5_text_2}</p>
        </div>
        <div>
            <i class="fas fa-lock"></i>
            <p>{$lang.landing_page_index_stl_5_text_3}</p>
        </div>
        <div>
            <i class="fas fa-chart-area"></i>
            <p>{$lang.landing_page_index_stl_5_text_4}</p>
        </div>
    </section>
    <section class="stl_6">
        <h2>{$lang.landing_page_index_stl_6_text_1}</h2>
        <div>
            <figure>
                <img src="{$path.images}index/stl_6_image_1.png" alt="Client">
            </figure>
            <figure>
                <img src="{$path.images}index/stl_6_image_2.png" alt="Client">
            </figure>
            <figure>
                <img src="{$path.images}index/stl_6_image_3.png" alt="Client">
            </figure>
            <figure>
                <img src="{$path.images}index/stl_6_image_4.png" alt="Client">
            </figure>
        </div>
    </section>
    <section class="stl_7">
        <div>
            <i class="fas fa-user-plus"></i>
            <h2>{$lang.landing_page_index_stl_7_text_1}</h2>
            <a href="/signup">{$lang.signup}</a>
        </div>
        <div>
            <i class="fas fa-th"></i>
            <h2>{$lang.landing_page_index_stl_7_text_2}</h2>
            <a href="/blog">{$lang.blog}</a>
        </div>
    </section>
    <section class="stl_8">
        <h2>{$lang.landing_page_index_stl_8_text_1}</h2>
        <div>
            <figure>
                <img src="{$path.images}index/stl_8_image_1.png" alt="Work team">
            </figure>
            <h3>Daniel Basurto</h3>
            <h4>{$lang.ceo}</h4>
        </div>
        <div>
            <figure>
                <img src="{$path.images}index/stl_8_image_2.png" alt="Work team">
            </figure>
            <h3>Alexa Zamora</h3>
            <h4>{$lang.coo}</h4>
        </div>
        <div>
            <figure>
                <img src="{$path.images}index/stl_8_image_3.png" alt="Work team">
            </figure>
            <h3>Gersón Gómez</h3>
            <h4>{$lang.cto}</h4>
        </div>
        <div>
            <figure>
                <img src="{$path.images}index/stl_8_image_4.png" alt="Work team">
            </figure>
            <h3>Saúl Poot</h3>
            <h4>{$lang.programmer}</h4>
        </div>
        <div>
            <figure>
                <img src="{$path.images}index/stl_8_image_5.png" alt="Work team">
            </figure>
            <h3>Alejandro Espinoza</h3>
            <h4>{$lang.programmer}</h4>
        </div>
        <div>
            <figure>
                <img src="{$path.images}index/stl_8_image_6.png" alt="Work team">
            </figure>
            <h3>David Gómez</h3>
            <h4>{$lang.programmer}</h4>
        </div>
    </section>
    <section class="stl_9">
        <h2>{$lang.landing_page_index_stl_9_text_1}</h2>
        <div>
            <figure>
                <img src="{$path.images}index/stl_9_image_1.png" alt="Partner">
            </figure>
            <figure>
                <img src="{$path.images}index/stl_9_image_2.png" alt="Partner">
            </figure>
            <figure>
                <img src="{$path.images}index/stl_9_image_3.png" alt="Partner">
            </figure>
            <figure>
                <img src="https://www.comparasoftware.com/wp-content/uploads/2019/05/comparasoftware_verificado.png" alt="Partner">
            </figure>
        </div>
    </section>
    <section class="stl_10">
        <div>
            <a href="https://facebook.com/guestvox" target="_blank"><i class="fab fa-facebook-square"></i></a>
            <a href="https://instagram.com/guestvox" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://linkedin.com/guestvox" target="_blank"><i class="fab fa-linkedin"></i></a>
            <a href="https://www.youtube.com/channel/UCKSAce4n1NqahbL5RQ8QN9Q" target="_blank"><i class="fab fa-youtube"></i></a>
        </div>
        <div>
            <a href="/about-us">{$lang.about_us}</a>
            <i class="fas fa-circle"></i>
            <a href="/terms-and-conditions">{$lang.terms_and_conditions}</a>
            <i class="fas fa-circle"></i>
            <a href="/privacy-policies">{$lang.privacy_policies}</a>
        </div>
        <p>Copyright<i class="far fa-copyright"></i>{$lang.all_right_reserved}</p>
    </section>
</main>
