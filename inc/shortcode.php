<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode [begrotingstool] — rendert het volledige 5-staps formulier.
 */

/**
 * Rendert één bedrag-invoerveld.
 *
 * @param string $id          Uniek veld-id (zonder 'bt-' prefix).
 * @param string $label       Zichtbaar label.
 * @param string $class       'bt-field-in' of 'bt-field-out'.
 * @param string $placeholder Optionele plaatshouder.
 * @param string $description Optionele HTML-beschrijving (voor lightbox).
 */
function bt_render_field( $id, $label, $class, $placeholder = '', $description = '' ) {
    $fid      = 'bt-' . esc_attr( $id );
    $has_desc = $description !== '';
    ?>
    <div class="bt-field">
        <div class="bt-label-wrap">
            <label class="bt-label" for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $label ); ?></label>
            <?php if ( $has_desc ) : ?>
            <button type="button"
                    class="bt-info-btn"
                    aria-label="<?php echo esc_attr( 'Meer informatie over ' . $label ); ?>"
                    aria-haspopup="dialog">?</button>
            <?php endif; ?>
        </div>
        <div class="bt-input-wrap">
            <span class="bt-euro" aria-hidden="true">€</span>
            <input type="text"
                   id="<?php echo esc_attr( $fid ); ?>"
                   class="<?php echo esc_attr( $class ); ?>"
                   inputmode="decimal"
                   autocomplete="off"
                   <?php if ( $placeholder !== '' ) echo 'placeholder="' . esc_attr( $placeholder ) . '"'; ?>>
        </div>
        <?php if ( $has_desc ) : ?>
        <div class="bt-field-description"><?php echo wp_kses_post( $description ); ?></div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Shortcode-callback.
 */
function bt_shortcode( $atts ) {
    try {
        return bt_render_tool( $atts );
    } catch ( Throwable $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            return '<div style="background:#fdd;padding:1em;border:2px solid #c00;font-family:monospace;font-size:.875rem">'
                . '<strong>Begrotingstool-fout:</strong> ' . esc_html( $e->getMessage() )
                . '<br><small>' . esc_html( basename( $e->getFile() ) ) . ':' . $e->getLine() . '</small>'
                . '</div>';
        }
        return '<p>De begrotingstool kon niet laden. Vernieuw de pagina of neem contact op.</p>';
    }
}
add_shortcode( 'begrotingstool', 'bt_shortcode' );

/**
 * Rendert de volledige tool; aangeroepen vanuit bt_shortcode() zodat fouten opvangbaar zijn.
 */
function bt_render_tool( $atts ) {
    $faq_pos = bt_get_faq_items( 'positief' );
    $faq_neg = bt_get_faq_items( 'negatief' );

    ob_start();
    ?>
    <div id="begrotingstool" class="bt-wrap">

        <?php /* ── Introductie ─────────────────────────────────────────── */ ?>
        <section class="bt-intro" aria-label="Introductie">
            <img src="<?php echo esc_url( GOGJ_URL . 'assets/img/logo-gripopjegeld.svg' ); ?>"
                 class="bt-logo"
                 alt="Grip op je geld"
                 width="250" />
            <div class="bt-intro__card">
                <h1>Waarom is het belangrijk inzicht te hebben in je inkomsten en uitgaven?</h1>
                <p>Wil je meer controle over je geld en weten waar het precies naar toe gaat? Pak je bank app erbij en vul de vragenlijst in. Dan weet jij binnen 15 minuten hoeveel je kan besparen en of je geld overhoudt voor de dingen die jij écht wil kopen.</p>
                <div class="bt-intro__action">
                    <button type="button" class="bt-btn bt-btn--start">Naar de tool</button>
                </div>
                <p class="bt-intro__more"><a id="bt-meer-info" href="#">Meer weten over deze tool?</a></p>
            </div>
        </section>

        <?php /* ── Formulier ───────────────────────────────────────────── */ ?>
        <div id="bt-form" hidden>

        <?php /* ── Voortgangsbalk ──────────────────────────────────────── */ ?>
        <div class="bt-progress"
             role="progressbar"
             aria-label="Formuliervoortgang"
             aria-valuenow="1"
             aria-valuemin="1"
             aria-valuemax="5">
            <p class="bt-progress__label">Stap 1 van 5</p>
            <div class="bt-progress__track">
                <div class="bt-progress__bar" style="width: 20%"></div>
            </div>
        </div>

        <?php /* ── Stap 1: Geld dat binnenkomt ─────────────────────────── */ ?>
        <div class="bt-step" data-step="1">
            <div class="bt-step__content">
                <h2>1. Geld dat binnenkomt</h2>
                <p>💰 Eerst: wat komt er elke maand binnen?<br />Vul hieronder de inkomsten in die voor jou gelden.</p>

                <?php bt_render_field( 'f5', 'Loon (netto)', 'bt-field-in', '',
                    '<h3>Loon (netto)</h3>
Je hebt vast wel eens gehoord van <b>netto</b> en <b>bruto</b>loon, maar wat betekent dat nou eigenlijk? <b>Brutoloon</b> is wat je verdient voordat de belasting eraf gaat. Nadat de belasting is betaald houd je <b>nettoloon</b> over &#8211; dat is het bedrag dat echt op je bankrekening komt.<br /><br />
Vul hier je <b>nettoloon</b> in, zodat je een goed overzicht krijgt wat je echt te besteden hebt.'
                ); ?>

                <?php bt_render_field( 'f48', 'Stagevergoeding', 'bt-field-in' ); ?>
                <?php bt_render_field( 'f6', 'Kleedgeld', 'bt-field-in' ); ?>
                <?php bt_render_field( 'f8', 'Zakgeld', 'bt-field-in' ); ?>

                <?php bt_render_field( 'f9', 'Zorgtoeslag', 'bt-field-in', '',
                    '<h3>Zorgtoeslag</h3>
Word je 18? Dan moet je een <b>zorgverzekering</b> hebben en daar betaal je elke maand voor. Heb je een lager inkomen? Dan is er <b>zorgtoeslag</b>: een bijdrage van de overheid om je te helpen je verzekering te betalen. <br /><br />Hoeveel je krijgt hangt af van wat je verdient. Op <a target="_blank" href="https://www.toeslagen.nl">www.toeslagen.nl</a> kun je kijken of je in aanmerking komt voor zorgtoeslag en kun je dit gelijk aanvragen.'
                ); ?>

                <?php bt_render_field( 'f10', 'Studiefinanciering', 'bt-field-in', '',
                    '<h3>Studiefinanciering</h3>
Ga je studeren (<b>MBO</b>, <b>HBO</b> of <b>WO</b>)? Dan kun je <b>studiefinanciering</b> krijgen via <b>DUO</b>. Dat kan uit verschillende elementen bestaan: de <b>basisbeurs</b>, de <b>aanvullende beurs</b>, een <b>lening</b> en het <b>studentenreisproduct</b>.<br /><br />
Wil je precies weten wat jij kunt krijgen? Check dan <a href="https://www.nibud.nl/onderwerpen/kinderen-en-jongeren/studeren/#Studiefinanciering" target="_blank">de website van het Nibud</a>.'
                ); ?>

                <?php bt_render_field( 'f11', 'Huurtoeslag', 'bt-field-in', '',
                    '<h3>Huurtoeslag</h3>
<b>Huurtoeslag</b> is geld van de overheid dat helpt om je huur te betalen. Je kunt dit krijgen als jouw inkomen onder een bepaalde grens ligt en je een <b>eigen woning</b> huurt.<br /><br />
Op <a href="https://www.belastingdienst.nl/wps/wcm/connect/nl/huurtoeslag/huurtoeslag" target="_blank">Huurtoeslag | Dienst Toeslagen</a> kun je kijken of je in aanmerking komt voor huurtoeslag en kun je dit gelijk aanvragen.'
                ); ?>

                <?php bt_render_field( 'f12', 'Overig', 'bt-field-in' ); ?>
            </div>
            <div class="bt-nav">
                <div class="bt-nav__inner">
                    <span></span>
                    <button type="button" class="bt-btn bt-btn--next" data-next-step="2">Volgende</button>
                </div>
            </div>
        </div>

        <?php /* ── Stap 2: Uitleg vaste / variabele lasten ──────────────── */ ?>
        <div class="bt-step" data-step="2" hidden>
            <div class="bt-step__content">
                <h2>2. Geld dat eruit gaat</h2>
                <p>Je hebt twee soorten uitgaven: <strong>vaste lasten en variabele lasten</strong>.</p>
                <p>🔒 <strong>Vaste lasten</strong> zijn kosten die steeds terugkomen. Meestal betaal je elke maand hetzelfde bedrag. Denk aan huur, zorgverzekering of je telefoonabonnement. Hiervoor heb je vaak een contract.</p>
                <p>🔄 <strong>Variabele lasten</strong> zijn kosten die verschillen. Het bedrag is elke keer anders en je betaalt ze niet elke maand. Bijvoorbeeld boodschappen, kleding of uitgaan.</p>
            </div>
            <div class="bt-nav">
                <div class="bt-nav__inner">
                    <button type="button" class="bt-btn bt-btn--prev" data-prev-step="1">Terug naar de vorige stap</button>
                    <button type="button" class="bt-btn bt-btn--next" data-next-step="3">Volgende</button>
                </div>
            </div>
        </div>

        <?php /* ── Stap 3: Vaste lasten ───────────────────────────────── */ ?>
        <div class="bt-step" data-step="3" hidden>
            <div class="bt-step__content">
                <h2>3. Vaste lasten</h2>
                <p>🔒 Dit zijn kosten die je sowieso hebt.<br />Vul de vaste lasten in die jij hebt:</p>

                <?php bt_render_field( 'f13', 'Huur', 'bt-field-out', '',
                    '<h3>Huur</h3>
Niet elke huurwoning is hetzelfde. Je hebt bijvoorbeeld <b>sociale huurwoningen</b>. Die zijn goedkoper en bedoeld voor mensen met een lager inkomen.<br /><br />
👉 Deze woningen worden aangeboden door een woningcorporatie in jouw stad. ⚠️ Let op: de <b>wachtlijsten zijn vaak lang</b>.<br /><br />
<b>Tip:</b> schrijf je zo snel mogelijk in, ook als je nog niet meteen wilt verhuizen. Dan bouw je alvast wachttijd op. Future you gaat je dankbaar zijn 😉'
                ); ?>

                <?php bt_render_field( 'f15', 'Energie, water, elektra', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f16', 'Internet', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f52', 'Telefoonabonnement', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f51', 'Opleiding (collegegeld/lesgeld)', 'bt-field-out' ); ?>

                <?php bt_render_field( 'f17', 'Zorgverzekering', 'bt-field-out', '',
                    '<h3>Zorgverzekering</h3>
Tot je 18e ben je gratis meeverzekerd bij je ouders. Word je 18? Dan moet <b>je zelf een zorgverzekering afsluiten</b>. Dat is verplicht in Nederland.<br />
1. Elke maand betaal je hiervoor premie<br />
2. De basisverzekering vergoedt o.a. huisarts en ziekenhuis<br />
3. Niet alles wordt vergoed (dat bepaalt de overheid)<br /><br />
Wil je extra dingen verzekerd hebben, zoals:<br />
- fysiotherapie<br />
- tandarts<br />
- bril of lenzen<br /><br />
👉 Dan kun je een <b>aanvullende verzekering</b> afsluiten. Deze is <b>niet verplicht</b>, maar soms wel handig.<br /><br />
💸 Bij je zorgverzekering hoort ook een <b>eigen risico</b>. Dat is het bedrag dat je eerst <b>zelf betaalt</b>, voordat je verzekering gaat meebetalen. Sommige mensen kiezen voor een <b>hoger eigen risico</b>:<br />
- je maandpremie wordt lager<br />
- maar: je betaalt meer als je ineens zorg nodig hebt<br /><br />
👉 Dit is alleen slim als je <b>genoeg spaargeld</b> hebt om dit op te vangen.'
                ); ?>

                <?php bt_render_field( 'f18', 'Aansprakelijkheidsverzekering', 'bt-field-out', '',
                    '<h3>Aansprakelijkheidsverzekering</h3>
Een <b>aansprakelijkheidsverzekering</b> betaalt schade die jij per ongeluk bij iemand anders veroorzaakt.<br /><br />Bijvoorbeeld: je laat iemands telefoon vallen, je morst drinken over een laptop of je maakt iets kapot bij iemand thuis.<br /><br />Zonder deze verzekering moet je die schade zelf betalen. Een aansprakelijkheidsverzekering kost vaak maar een paar euro per maand.'
                ); ?>

                <?php bt_render_field( 'f19', 'Inboedelverzekering', 'bt-field-out', '',
                    '<h3>Inboedelverzekering</h3>
Met een <b>inboedelverzekering</b> verzeker je jouw spullen in huis. Als er in je huis iets met deze spullen gebeurt, door bijvoorbeeld brand of waterschade, krijg je als je verzekerd bent (een deel van) de schade vergoed.'
                ); ?>

                <?php bt_render_field( 'f54', 'Overige verzekeringen', 'bt-field-out', '',
                    '<h3>Overige verzekeringen</h3>
Er zijn twee verzekeringen die <b>verplicht</b> zijn in Nederland:<br />
- Zorgverzekering (basisverzekering) &#8211; voor iedereen vanaf 18<br />
- WA-verzekering voor auto of bromfiets &#8211; alleen als je er één hebt<br /><br />
Andere verzekeringen zijn <b>niet</b> verplicht, maar kunnen wel slim zijn:<br />
- Aansprakelijkheidsverzekering<br />
- Inboedelverzekering<br />
- Reisverzekering<br />
- Uitvaartverzekering<br />
- Huisdierenverzekering<br /><br />
Welke verzekering bij jou past, hangt af van jouw situatie.'
                ); ?>

                <?php bt_render_field( 'f55', 'Overige', 'bt-field-out' ); ?>
            </div>
            <div class="bt-nav">
                <div class="bt-nav__inner">
                    <button type="button" class="bt-btn bt-btn--prev" data-prev-step="2">Terug naar de vorige stap</button>
                    <button type="button" class="bt-btn bt-btn--next" data-next-step="4">Volgende</button>
                </div>
            </div>
        </div>

        <?php /* ── Stap 4: Variabele kosten ────────────────────────────── */ ?>
        <div class="bt-step" data-step="4" hidden>
            <div class="bt-step__content">
                <h2>4. Variabele lasten</h2>
                <p>🔄 Vul hieronder de variabele lasten in die voor jou gelden. Deze kosten zijn elke maand anders, dus soms moet je een schatting maken. Kijk daarvoor naar wat je vorige maand hebt uitgegeven.</p>

                <?php bt_render_field( 'f56', 'Boodschappen', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f23', 'Vervoer (auto, scooter, trein)', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f25', 'Kleding en schoenen', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f26', 'Sport', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f27', 'Uitgaan', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f28', 'Uitjes (bioscoop, dierentuin, etc.)', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f29', 'Gamen', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f30', 'Schoolspullen en schoolboeken', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f31', 'Cadeaus', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f32', 'Abonnementen Spotify, Netflix, etc.', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f33', 'Klarna/Afterpay/Creditcard rekeningen', 'bt-field-out' ); ?>
                <?php bt_render_field( 'f36', 'Overig', 'bt-field-out' ); ?>
            </div>
            <div class="bt-nav">
                <div class="bt-nav__inner">
                    <button type="button" class="bt-btn bt-btn--prev" data-prev-step="3">Terug naar de vorige stap</button>
                    <button type="button" class="bt-btn bt-btn--next" data-next-step="5">Volgende</button>
                </div>
            </div>
        </div>

        <?php /* ── Stap 5: Resultaat ───────────────────────────────────── */ ?>
        <div class="bt-step" data-step="5" hidden>
            <div class="bt-step__content">
                <h2>5. Jouw resultaat</h2>
                <p>Hoeveel hou jij per maand over?</p>

                <div id="budget-summary">
                    <div class="dashboard-header">
                        <div class="saldo-box" aria-live="polite" aria-atomic="true">
                            <div class="total">€&nbsp;<span id="bt-maand-saldo">0</span></div>
                            <div class="sublabel" id="bt-saldo-label">houdt je per maand over</div>
                        </div>
                        <p class="result-positive"><b>Lekker bezig!</b> 🎉<br>Je geeft minder uit dan je binnenkrijgt. Gebruik de vragen en tips hieronder om te zorgen dat je dit volhoudt en bewust te kiezen wat je met je geld wilt doen.</p>
                        <p class="result-negative">Uit je begroting blijkt dat je <b>meer</b> uitgeeft dan je binnenkrijgt. Door de vragen en tips hieronder te bekijken, kom je erachter waar het knelt en wat je hieraan kunt doen. Er bestaan ook veel organisaties die met je mee kunnen denken. Je hoeft het niet alleen te doen!</p>
                    </div>

                    <div class="dashboard-table" id="bt-budget-table"></div>

                    <div class="budget-download">
                        <button type="button" id="downloadPdf" class="budget-download__btn" aria-label="Download jouw resultaat als PDF-bestand">
                            <svg aria-hidden="true" focusable="false" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Download jouw resultaat (PDF)
                        </button>
                    </div>
                </div>

                <div class="accordion accordion--positief">
                    <h3 class="accordion-title">Tips &amp; vragen</h3>
                    <?php bt_render_accordion_items( $faq_pos, 'pos' ); ?>
                </div>

                <div class="accordion accordion--negatief">
                    <h3 class="accordion-title">Tips &amp; vragen</h3>
                    <?php bt_render_accordion_items( $faq_neg, 'neg' ); ?>
                </div>

                <input type="hidden" id="bt-totaal-inkomsten">
                <input type="hidden" id="bt-totaal-uitgaven">
                <input type="hidden" id="bt-saldo">
            </div>
            <div class="bt-nav">
                <div class="bt-nav__inner">
                    <button type="button" class="bt-btn bt-btn--prev" data-prev-step="4">Terug naar de vorige stap</button>
                </div>
            </div>
        </div>

        </div><?php /* #bt-form */ ?>

    </div><?php /* .bt-wrap */ ?>
    <?php
    return ob_get_clean();
}
