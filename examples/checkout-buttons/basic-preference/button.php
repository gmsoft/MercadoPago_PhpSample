<?php
require_once "../../../lib/mercadopago.php";

$mp = new MP("4991808988306545", "rX9g6Jrcxptc9j8m4eCHZTkb7u232zRb");

$preference_data = array(
    "items" => array(
        array(
            "title" => "Title of what you are paying for",
            "currency_id" => "ARS",
            "category_id" => "Category",
            "quantity" => 1,
            "unit_price" => 10.2
        )
    )
);

$preference = $mp->create_preference($preference_data);
?>

<!doctype html>
<html>
    <head>
        <title>MercadoPago SDK - Create Preference and Show Checkout Example</title>
    </head>
    <body>
       	<a href="<?php echo $preference["response"]["init_point"]; ?>" name="MP-Checkout" class="orange-ar-m-sq-arall">Pagar</a>
        <script type="text/javascript" src="//resources.mlstatic.com/mptools/render.js"></script>
    </body>
</html>
