<!doctype html>
<html>
    <head>
        <title>MercadoPago</title>
    </head>
    <body>
        <hr/>

        <table border='1'>
            <tr><th>id</th><th>external_reference</th><th>status</th></tr>
            <?php
            foreach ($searchResult["response"]["results"] as $payment) {
                ?>
                <tr>
                    <td><?php echo $payment["collection"]["id"]; ?></td>
                    <td><?php echo $payment["collection"]["external_reference"]; ?></td>
                    <td><?php echo $payment["collection"]["status"]; ?></td>
                </tr>
                <?php
            }

            ?>
        </table>
        <?php
            echo "<pre>";
            print_r($searchResult["response"]["results"]);
        ?>        
    </body>
</html>

