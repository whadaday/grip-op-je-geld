<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * FAQ-items per resultaat (positief = geld over, negatief = tekort).
 * Één bron voor zowel de accordion als de PDF-export.
 *
 * @param string $type 'positief' of 'negatief'
 * @return array
 */
function bt_get_faq_items( $type = 'positief' ) {

    $positief = array(
        array(
            'title'   => 'Waardoor hou jij geld over?',
            'content' => 'Sta even stil bij wat ervoor zorgt dat je geld overhoudt, zoals bewuste keuzes in je uitgaven of lage vaste lasten. Kijk daarna naar je uitgaven en bedenk welke echt nodig zijn en welke meer \'nice to have\' zijn. Ook als het nu goed gaat, kan dit helpen om nog wat extra ruimte te creëren of je situatie stabiel te houden.',
        ),
        array(
            'title'   => 'Krijg je al het geld waar je recht op hebt?',
            'content' => 'Veel jongeren laten geld liggen, zonder dit te weten. Check of je recht hebt op toeslagen, zoals zorgtoeslag of huurtoeslag, of bijvoorbeeld op studiefinanciering. Dit kan je inkomsten per maand direct verhogen. Op <a href="https://www.belastingdienst.nl/wps/wcm/connect/nl/toeslagen/content/hulpmiddel-proefberekening-toeslagen" target="_blank">Proefberekening toeslagen | Dienst Toeslagen</a> kun je berekenen of je recht hebt op toeslagen, en op <a href="https://www.nibud.nl/onderwerpen/kinderen-en-jongeren/studeren/#Studiefinanciering" target="_blank">de website van het Nibud</a> of je recht hebt op studiefinanciering.',
        ),
        array(
            'title'   => 'Hou je voldoende buffer over?',
            'content' => 'Heel mooi dat jij geld overhoudt per maand, maar hou jij ook voldoende buffer per maand over voor onverwachte kosten. Bijvoorbeeld als je zorg nodig hebt dat niet wordt gedekt door je zorgverzekering. Of als je een nieuwe winterjas nodig hebt. Als richtlijn kun je aanhouden dat je 10% van je inkomsten als buffer houdt. Heb je dat nog niet? Kijk eens naar je begroting om te kijken of je geld kunt besparen, zie ook de volgende tip.',
        ),
        array(
            'title'   => 'Wat wil je doen met het geld dat je overhoudt?',
            'content' => 'Hou je na je buffer nog steeds geld over? Denk na waar jij dit geld het liefst voor gebruikt: sparen, een vakantie, kleding. Je kunt voor je spaardoelen ook spaarpotjes maken in je bank app en instellen dat er elke maand automatisch een bedrag naar je spaarrekening gaat. Zo voorkom je dat je geld ineens op is zonder dat je het doorhebt.',
        ),
        array(
            'title'   => 'Wie of wat kan je helpen?',
            'content' => 'We hebben allemaal wel eens hulp nodig met onze geldzaken. Er zijn gelukkig veel plekken waar je terecht kunt voor vragen. Praat er bijvoorbeeld over met iemand die je vertrouwt, zoals je ouders/verzorgers of een mentor. Ook is er gratis hulp in jouw gemeente, zoek bijvoorbeeld online naar een wijkteam bij jou in de buurt.',
        ),
    );

    $negatief = array(
        array(
            'title'   => 'Wat gebeurt er als je zo doorgaat?',
            'content' => 'Je ziet hierboven hoeveel geld je afgelopen maand tekort kwam. Kijk een paar maanden vooruit en bedenk wat dit betekent als er niets verandert: na drie of zes maanden kan dit bedrag flink zijn opgelopen. Onderneem nu actie, zodat je later niet met stress of schulden komt te zitten.',
        ),
        array(
            'title'   => 'Als je kijkt naar jouw uitgaven, wat is \'need to have\' en wat is \'nice to have\'?',
            'content' => 'Sommige uitgaven voelen noodzakelijk, maar zijn dat niet altijd. Kijk eens welke uitgaven <i>nice to have</i> zijn en waarop je kunt besparen. Hoeveel geld hou je dan per maand over?',
        ),
        array(
            'title'   => 'Krijg je al het geld waar je recht op hebt?',
            'content' => 'Veel jongeren laten geld liggen, zonder dit te weten. Check of je recht hebt op toeslagen, zoals zorgtoeslag of huurtoeslag, of bijvoorbeeld op studiefinanciering. Dit kan je inkomsten per maand direct verhogen. Op <a href="https://www.belastingdienst.nl/wps/wcm/connect/nl/toeslagen/content/hulpmiddel-proefberekening-toeslagen" target="_blank">Proefberekening toeslagen | Dienst Toeslagen</a> kun je berekenen of je recht hebt op toeslagen, en op <a href="https://www.nibud.nl/onderwerpen/kinderen-en-jongeren/studeren/#Studiefinanciering" target="_blank">de website van het Nibud</a> of je recht hebt op studiefinanciering.',
        ),
        array(
            'title'   => 'Hoeveel kun je per week besteden?',
            'content' => 'Reken uit hoeveel je met jouw huidige inkomsten per week kan besteden. Zorg dat je niet alles opmaakt, zodat je een buffer kan opbouwen voor onverwachte kosten. Bijvoorbeeld als je zorg nodig hebt dat niet wordt gedekt door je zorgverzekering. Of als je een nieuwe winterjas nodig hebt. Als richtlijn kun je aanhouden dat je 10% van je inkomsten niet uitgeeft. Spreek met jezelf een weekbudget af, zodat je zeker weet dat je niet meer uitgeeft dan je hebt.',
        ),
        array(
            'title'   => 'Wie of wat kan je helpen?',
            'content' => 'We hebben allemaal wel eens hulp nodig met onze geldzaken. Het is zonde om te wachten met hulp vragen, voorkom dat je vaker in de min uitkomt. Er zijn gelukkig veel plekken waar je terecht kunt voor vragen. Praat er bijvoorbeeld over met iemand die je vertrouwt, zoals je ouders/verzorgers of een mentor. Ook is er gratis hulp in jouw gemeente, zoek bijvoorbeeld online naar een wijkteam bij jou in de buurt.',
        ),
    );

    return $type === 'negatief' ? $negatief : $positief;
}

/**
 * Rendert accordion-items als HTML.
 *
 * @param array  $items  Resultaat van bt_get_faq_items().
 * @param string $prefix Uniek prefix voor id-attributen.
 */
function bt_render_accordion_items( $items, $prefix ) {
    foreach ( $items as $index => $item ) {
        $content_id = 'bt-acc-content-' . $prefix . '-' . $index;
        $header_id  = 'bt-acc-header-' . $prefix . '-' . $index;
        ?>
        <div class="accordion-item">
            <button type="button"
                    id="<?php echo esc_attr( $header_id ); ?>"
                    class="accordion-header"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr( $content_id ); ?>">
                <span><?php echo esc_html( $item['title'] ); ?></span>
                <span class="accordion-icon" aria-hidden="true"></span>
            </button>
            <div class="accordion-content"
                 id="<?php echo esc_attr( $content_id ); ?>"
                 role="region"
                 aria-labelledby="<?php echo esc_attr( $header_id ); ?>">
                <p><?php echo wp_kses_post( $item['content'] ); ?></p>
            </div>
        </div>
        <?php
    }
}
