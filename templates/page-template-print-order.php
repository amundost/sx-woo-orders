<?php
/*
Template Name: Print Orders
*/

// Sjekk om brukeren har tilgang
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Hent ordre-ID fra URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Sjekk om ordre-ID er gyldig
if ($order_id == 0) {
    wp_die('No order specified.');
}

// Hent ordren
$order = wc_get_order($order_id);

// Sjekk om ordren finnes
if (!$order) {
    wp_die('Order not found.');
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        .woocommerce-order {
            max-width: 800px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }

        .woocommerce-order .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .woocommerce-order table {
            width: 90%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin: auto;
        }

        .woocommerce-order table,
        .woocommerce-order th,
        .woocommerce-order td {
            border: 1px solid #bbb;
        }

        .woocommerce-order th,
        .woocommerce-order td {
            padding: 8px;
            text-align: left;
        }

        .woocommerce-order .addresses {
            width: 90%;
            display: flex;
            justify-content: space-between;
            margin: 1rem auto 1rem auto;
        }

        .woocommerce-order .address {
            width: 48%;
            padding: 10px;
            border: 1px solid #bbb;
        }

        .page-break {
            page-break-before: always;
        }

        .instructions {
            text-align: left;
            margin-top: 50px;
            max-width: 800px;
            margin: auto;
        }

        .instructions img {
            float: left;
            max-width: 200px;
            padding: 1rem;
        }

        .instructions .text {
            padding: 2rem;
        }

        .frame {
            border: 1px solid #bbb;
            padding: 0 1rem 0 1rem;
        }

        .order_header {
            width: 90%;
            margin: 1rem auto 1rem auto;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <div class="woocommerce-order">
        <?php
        // Ordreoverskrift
        echo '<div class="header"><h1>Ordrebekreftelse</h1></div>';
        echo '<h3 class="order_header">[Ordrenr: #' . $order->get_id() . '] (' . date_i18n('j. F Y', strtotime($order->get_date_created())) . ')</h3>';

        // Ordrelinjer og totalsummer i samme tabell
        echo '<table>';
        echo '<thead><tr><th>Produkt</th><th>Antall</th><th>Pris</th><th>Total</th></tr></thead>';
        echo '<tbody>';
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $total = $item->get_total();
            $price = $item->get_subtotal() / $quantity;
            echo '<tr>';
            echo '<td>' . esc_html($product_name) . '</td>';
            echo '<td>' . esc_html($quantity) . '</td>';
            echo '<td>' . wc_price($price) . '</td>';
            echo '<td>' . wc_price($total) . '</td>';
            echo '</tr>';
        }

        // Totalsummer
        foreach ($order->get_order_item_totals() as $key => $total) {
            echo '<tr>';
            echo '<th colspan="3">' . esc_html($total['label']) . '</th>';
            echo '<td>' . wp_kses_post($total['value']) . '</td>';
            echo '</tr>';
        }

        // Betalingsmåte
        $payment_method = $order->get_payment_method_title();
        echo '<tr>';
        echo '<th colspan="3">Betalingsmåte</th>';
        echo '<td>' . esc_html($payment_method) . '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

        // Faktura- og leveringsadresse i to kolonner
        echo '<div class="addresses">';
        echo '<div class="address">';
        echo '<h3>Fakturaadresse</h3>';
        echo '<p>' . $order->get_formatted_billing_address() . '</p>';
        echo '</div>';

        if ($order->get_formatted_shipping_address()) {
            echo '<div class="address">';
            echo '<h3>Leveringsadresse</h3>';
            echo '<p>' . $order->get_formatted_shipping_address() . '</p>';
            echo '</div>';
        }
        echo '</div>';
        ?>
    </div>

    <div class="page-break"></div>

    <div class="instructions">
        <?php
        // Hent logoen fra WordPress-tilpasningsinnstillingene
        if (function_exists('get_custom_logo') && has_custom_logo()) {
            $custom_logo_id = get_theme_mod('custom_logo');
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if (isset($logo[0])) {
                echo '<img src="' . esc_url($logo[0]) . '" alt="Logo">';
            }
        }
        ?>
        <div class="text">
            <p>
                <b>Takk for kjøpet!</b><br />
                Vi håper ditt nye hoppetau tilfredsstiller dine behov. Her er
                en sjekkliste med det du bør gjøre først for å sikre at
                hoppetauet passer perfekt for deg:
            </p>
            <p>
                <b>Mål Tauets Lengde: </b>Stå midt på tauet og sjekk at
                håndtakene når deg opp til eller rett under armhulene.
            </p>

            <div class="frame">
                <p>
                    <b>Juster Lengden Perle-tau: </b>Trekk ut snoren fra håndtaket, løsne knuten, fjern
                    skiven og håndtaket, og juster lengden ved å fjerne ekstra perler. Sett håndtaket
                    og skiven tilbake, knytt en ny knute, og klipp av overflødig snor.
                </p>
                <p>
                    <b>Juster Lengden PVC-tau: </b>Trekk snoren fra toppen av håndtaket. Fjern
                    stopperen som holder snoren på plass. Trekk snoren gjennom håndtaket til du
                    når ønsket lengde. Klipp av overflødig snor. Tre snor gjennom håndtaket igjen.
                    Sett tilbake stopperen for å sikre snoren i håndtaket.
                </p>
            </div>

            <p>
                <b>Sjekk Kvaliteten: </b>Inspiser snoren for skader. Er snoren frynsete etter kutting, kan du
                forsegle den ved å bruke en lighter eller fyrstikk for å gi en ren finish. Dette vil også
                forhindre at knuter går opp.
            </p>
            <p>
                <b>Hopp Tau:</b>Nå er tauet klar til å brukes. Hopp på et flatt underlag og prøv noen hopp for
                å sikre forsikre deg om at lengden og justeringene komfortable.
            </p>
            <p>
                <b>Hoppe Steg: </b>Grunn hopp, løpesteg, en fot hopp og kryss hopp er eksempler på
                grunnlegende steg du kan begynne med. Om du ikke har hoppet tau før anbefaler vi at
                du ikke hopper mer enn 5-10 minutter den første tiden, øk med tiden.
            </p>
            <p>
                <b>Lagring: </b>Oppbevar tauet tørt og rengjør det jevnlig.
            </p>
            <p>
                Vi håper denne sjekklisten hjelper deg med å få mest mulig ut av ditt nye hoppetau.
                Forslag til øvelser og runtine/combo kan du finne på @kristiane.skips , @tempo.tau på
                instagram og www.kristianeskips.com samt vår YouTube kanal, kristiane.skips
            </p>
            <p>God trening!</p>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>

</html>