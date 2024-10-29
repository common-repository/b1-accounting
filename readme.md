Įskiepis skirtas sinchronizuoti produktus ir užsakymus tarp WooCommerce ir B1.lt.

### Reikalavimai ###

* PHP >=7.0
* Wordpress >4.9.4
* WooCommerce >3.3.1
* MariaDB 10.2 / MySQL 5.7

### Diegimas ###

* Suinstaliuokite iskiepį iš woocommerce svetainės
* Administracijos skiltyje įdiekite modulį ir suveskite reikiamą informaciją.
* Išsaugokite pakeitimus.
* Paleiskite prekių sinchronizavimo komandą.
* `NEBŪTINA` Paleiskite užsakymų sinchronizavimo komandą.
* Norėdami, kad pirkėjai matytų B1 sugeneruotas sąskaitas, reikia `wp-content\plugins\woocommerce\templates\myaccount\orders.php` faile norimoje puslapio vietoje įterpti nuorodą pvz. 

```
#!php

<?php  
if (in_array('b1-accounting/b1-accounting.php', apply_filters('active_plugins', get_option('active_plugins'))) && $order->get_status() == 'completed') {
?>
<a target="_new" href='<?php echo admin_url() . 'admin-post.php?action=b1_download_invoice&order_id=' . $order->id . '&key=' . $order->post->post_password ?>'>PDF</a>
<?php } ?>

```

### Pastabos ####

Į B1 siunčiami TIK užsakymai su statusu "Completed" / "Įvykdyta" (reikšmė 'wc-completed').
Užsakymo data yra laikoma ta, kuri yra nurodyta prie užsakymo e.parduotuvėje. Norint, kad data sutaptų su mokėjimu, prieš patvirtinant užsakymą reikia pakeisti ir šią datą. 

### Kontaktai ###

* Kilus klausimams, prašome kreiptis info@b1.lt