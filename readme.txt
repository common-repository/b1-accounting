=== B1.lt ===
Contributors: b1accounting
Tags: orders, accounting
Requires at least: 4.9.4
Tested up to: 6.0.3
Requires PHP: 7.0
Stable tag: trunk

Įskiepis skirtas sinchronizuoti produktus ir užsakymus tarp WooCommerce ir B1.lt.

== Description ==
Įskiepis skirtas sinchronizuoti produktus ir užsakymus tarp WooCommerce ir B1.lt.

== Installation ==
### Reikalavimai ###

* PHP >=7.0
* WordPress >4.9.4
* WooCommerce >3.3.1
* MariaDB 10.2 / MySQL 5.7

### Diegimas ###

* Suinstaliuokite iskiepį iš WordPress įskiepių parduotuvės.
* Administracijos skiltyje įdiekite modulį ir suveskite reikiamą informaciją.
* Išsaugokite pakeitimus.
* Paleiskite prekių sinchronizavimo komandą.
* **NEBŪTINA** Paleiskite užsakymų sinchronizavimo komandą.
* Norėdami, kad pirkėjai matytų B1 sugeneruotas sąskaitas, reikia faile `wp-content \ plugins \ woocommerce \ templates \ myaccount \ orders.php` norimoje puslapio vietoje įterpti nuorodą pvz.
[^1]:
~~~~
<?php
if (in_array('b1-accounting/b1-accounting.php', apply_filters('active_plugins', get_option('active_plugins'))) && $order->get_status() == 'completed') {
?>
<a target="_new" href='<?php echo admin_url() . 'admin-post.php?action=b1_download_invoice&order_id=' . $order->id . '&key=' . $order->post->post_password ?>'>PDF</a>
<?php } ?>
~~~~

* Prie serverio Cron darbų sąrašo pridėkite visus išvardintus cron darbus, nurodytus modulio konfigūravimo puslapyje.
  Pridėti cron darbus galite per serverio valdymo panelę (DirectAdmin, Cpanel) arba įvykdę šias komandines eilutes serverio pusėje
~~~~
*/5 * * * * wget -O /dev/null -q 'https://example.com/wp-cron.php'
~~~~


### Pastabos ####

Į B1 siunčiami TIK užsakymai su statusu "Completed" / "Įvykdyta" (reikšmė 'wc-completed').
Užsakymo data yra laikoma ta, kuri yra nurodyta prie užsakymo e.parduotuvėje. Norint, kad data sutaptų su mokėjimu, prieš patvirtinant užsakymą reikia pakeisti ir šią datą.

### Kontaktai ###

* Kilus klausimams, prašome kreiptis info@b1.lt

== Changelog ==
No changes

== Upgrade Notice ==
No notes

== Screenshots ==
1. Settings configuration page
2. Custom field mapping
