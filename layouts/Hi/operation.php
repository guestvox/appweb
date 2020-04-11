<?php

defined('_EXEC') or die;

$this->dependencies->add(['css', '{$path.css}Hi/operation.css']);
$this->dependencies->add(['js', '{$path.js}Hi/operation.js']);
$this->dependencies->add(['js', '{$path.plugins}owl-carousel/owl.carousel.min.js']);
$this->dependencies->add(['css', '{$path.plugins}owl-carousel/assets/owl.carousel.min.css']);
$this->dependencies->add(['css', '{$path.plugins}owl-carousel/assets/owl.theme.default.min.css']);
$this->dependencies->add(['css', '{$path.plugins}fancy-box/jquery.fancybox.min.css']);
$this->dependencies->add(['js', '{$path.plugins}fancy-box/jquery.fancybox.min.js']);

?>

<main class="landing-page-operation">
    <header class="topbar">
        <figure>
            <img src="{$path.images}logotype-white.png" alt="GuestVox">
        </figure>
        <nav>
            <ul>
                <li><a class="btn" data-button-modal="contact">¡Solicita tu demo ahora!</a></li>
            </ul>
        </nav>
    </header>
    <section class="main">
        <header class="cover">
            <div class="container">
                <div class="content">
                    <h1>Solución hotelera en la nube, para la optimización de operaciones y mejora de reputación en línea.</h1>
                    <h2>Gestiona correctamente las incidencias y peticiones de tus huéspedes.</h2>
                    <a class="btn" data-button-modal="contact">¡Solicita tu demo ahora!</a>
                </div>
                <figure>
                    <img src="{$path.images}hi/operation/screen-1.jpg" alt="Software">
                </figure>
            </div>
            <div class="rocket"></div>
        </header>
        <section class="container background">
            <div class="space50"></div>
            <div class="title">
                <h2>¿Qué hacemos?</h2>
                <p>...</p>
            </div>
            <div class="space50"></div>
            <div class="boxes three-boxes">
                <div class="box">
                    <span class="icon-communication"></span>
                    <h4>Gestionamos las órdenes de trabajo entre departamentos y mejoramos su comunicación.</h4>
                </div>
                <div class="box">
                    <span class="icon-clients"></span>
                    <h4>Recibimos y damos seguimiento a las peticiones, comentarios y quejas de los huéspedes.</h4>
                </div>
                <div class="box">
                    <span class="icon-like"></span>
                    <h4>Recibimos la retroalimentación de tus huéspedes y los invitamos a dejar una evaluación en línea.</h4>
                </div>
            </div>
            <div class="space100"></div>
            <div class="title">
                <h2>¿Cómo funciona GuestVox?</h2>
                <p>...</p>
            </div>
            <div class="space50"></div>
            <div class="boxes-product">
                <div class="box">
                    <figure>
                        <img src="{$path.images}hi/operation/icon-lock.svg" alt="" width="70" height="70">
                    </figure>
                    <h4>Credenciales encriptadas</h4>
                    <p>Los colaboradores acceden fácilmente a la plataforma utilizando sus credenciales de acceso (usuario y contraseña).</p>
                </div>
                <div class="box">
                    <figure>
                        <img src="{$path.images}hi/operation/icon-person.svg" alt="" width="70" height="70">
                    </figure>
                    <h4>Asigna al área y/o persona responsable del seguimiento.</h4>
                    <p>Crea una incidencia (Vox), y asigna al área o persona responsable del seguimiento.</p>
                </div>
                <div class="box">
                    <figure>
                        <img src="{$path.images}hi/operation/icon-attachment.svg" alt="" width="70" height="70">
                    </figure>
                    <h4>Adjunta imágenes, videos, archivos PDF, Word y Excel.</h4>
                    <p>Enriquece el Vox y respalda tu información con archivos adjuntos.</p>
                </div>
                <div class="box">
                    <figure>
                        <img src="{$path.images}hi/operation/icon-person-follow.svg" alt="" width="70" height="70">
                    </figure>
                    <h4>Seguimiento de acuerdo a prioridad y tiempo transcurrido.</h4>
                    <p>Una vez creada la incidencia, los departamentos asignados dan seguimiento a cada caso.</p>
                </div>
                <div class="box">
                    <figure>
                        <img src="{$path.images}hi/operation/icon-time.svg" alt="" width="70" height="70">
                    </figure>
                    <h4>Tu información siempre en tiempo real.</h4>
                    <p>Visualiza minuto a minuto el estatus de tu hotel, el seguimiento a los Voxes y la satisfacción de tus huéspedes.</p>
                </div>
                <div class="box">
                    <figure>
                        <img src="{$path.images}hi/operation/icon-multi-device.svg" alt="" width="70" height="70">
                    </figure>
                    <h4>Accede desde cualquier dispositivo.</h4>
                    <p>No importa dónde estes, ni que dispositivo uses, accesa a tu información cuando quieras y donde quieras.</p>
                </div>
            </div>
        </section>
        <section class="call-to-action">
            <div class="container">
                <div class="content">
                    <h4>Ahorra hasta un 20% de costos operativos</h4>
                </div>
                <a data-button-modal="contact">¡Solicita tu demo ahora!</a>
            </div>
        </section>
        <section class="container background">
            <div class="title">
                <h2>Screenshots</h2>
                <p>Un vistazo a nuestro software</p>
            </div>
            <div class="space50"></div>
            <div id="screenshots" class="owl-carousel owl-theme">
                <div class="item" style="background-image: url('{$path.images}hi/operation/screenshot-1-thumb.jpg');">
                    <a data-fancybox="gallery" href="{$path.images}hi/operation/screenshot-1.jpg"></a>
                </div>
                <div class="item" style="background-image: url('{$path.images}hi/operation/screenshot-2-thumb.jpg');">
                    <a data-fancybox="gallery" href="{$path.images}hi/operation/screenshot-2.jpg"></a>
                </div>
                <div class="item" style="background-image: url('{$path.images}hi/operation/screenshot-3-thumb.jpg');">
                    <a data-fancybox="gallery" href="{$path.images}hi/operation/screenshot-3.jpg"></a>
                </div>
                <div class="item" style="background-image: url('{$path.images}hi/operation/screenshot-4-thumb.jpg');">
                    <a data-fancybox="gallery" href="{$path.images}hi/operation/screenshot-4.jpg"></a>
                </div>
                <div class="item" style="background-image: url('{$path.images}hi/operation/screenshot-5-thumb.jpg');">
                    <a data-fancybox="gallery" href="{$path.images}hi/operation/screenshot-5.jpg"></a>
                </div>
            </div>
        </section>
    </section>
    <footer class="main">
        <div class="container">
            <h2>Haz inteligencia de negocio</h2>
            <h3>Y ahorra hasta un 20% en costos, incrementa tu reputación en linea, calidad de servicio, productividad de colaboradores e ingresos.</h3>
            <a class="btn" data-button-modal="contact">¡SOLICITA TU DEMO AHORA!</a>
            <ul class="social_media">
                <li><a href="https://www.facebook.com/Guestvox/" target="_blank">Facebook</a></li>
                <li><a href="https://www.instagram.com/guestvox/" target="_blank">Instagram</a></li>
                <li><a href="https://www.linkedin.com/company/guestvox/" target="_blank">LinkedIn</a></li>
                <li><a href="https://www.youtube.com/channel/UCKSAce4n1NqahbL5RQ8QN9Q" target="_blank">YouTube</a></li>
            </ul>
            <div class="copyright">
                <strong>guestvox.com</strong> Todos los derechos reservados.
            </div>
        </div>
    </footer>
</main>
<section class="modal" data-modal="contact">
    <div class="content">
        <header>
            <h3>Pongamonos en contacto</h3>
        </header>
        <main>
            <form name="contact">
                <div class="row">
                    <div class="span12">
                        <div class="label">
                            <label>
                                <p>Nombre del negocio</p>
                                <input type="text" name="business" />
                            </label>
                        </div>
                    </div>
                    <div class="span12">
                        <div class="label">
                            <label>
                                <p>Tipo de negocio</p>
                                <select name="type">
                                    <option value="" selected hidden>Selecciona una opción</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="restaurant">Restaurante</option>
                                    <option value="others">Otros</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="span4 hidden">
                        <div class="label">
                            <label>
                                <p>Número de habitaciones</p>
                                <input type="number" name="rooms" />
                            </label>
                        </div>
                    </div>
                    <div class="span4 hidden">
                        <div class="label">
                            <label>
                                <p>Número de mesas</p>
                                <input type="number" name="tables" />
                            </label>
                        </div>
                    </div>
                    <div class="span12">
                        <div class="label">
                            <label>
                                <p>Nombre de contacto</p>
                                <input type="text" name="contact" />
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>Correo electrónico</p>
                                <input type="text" name="email" />
                            </label>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="label">
                            <label>
                                <p>Teléfono</p>
                                <input type="text" name="phone" />
                            </label>
                        </div>
                    </div>
                    <div class="span12">
                        <p style="font-size: 10px; line-height: 1.3; opacity: 0.5; margin-bottom: 0px;">Al enviar este formulario, solicitará que un representante de Guestvox S.A.P.I de C.V. se ponga en contacto con usted por teléfono o correo electrónico en los próximos días.</p>
                    </div>
                </div>
            </form>
        </main>
        <footer>
            <div class="action-buttons">
                <button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
                <button class="btn btn-colored" button-success>¡Contáctenme!</button>
            </div>
        </footer>
    </div>
</section>
