<?PHP

	/*
		@Author Adam Cox

		This is a simple example of a bot that will make minimum orders on ETH to close the spread.

		TODO
		 - allow for any market or array of markets or all markets
	*/

	function light_show( $Adapter ) {
		echo "*** " . get_class( $Adapter ) . " Light Show ***\n";

		//usleep( 100000 );

		/*$eth_open_orders = $Adapter->get_open_orders( "BTC-ETH" );
		foreach( $eth_open_orders as $eth_open_order ) {
			print_r( $Adapter->cancel($eth_open_order['id'], array( 'market' => $eth_open_order['market'] ) ) );
		}*/

		//_____get the markets to loop over:

		$eth_market = $Adapter->get_market_summary( "BTC-ETH" );

		$btc_bal_arr = $Adapter->get_balance( "BTC", array( 'type' => 'exchange' ) );
		$btc_bal = $btc_bal_arr['available'];

		$eth_bal_arr = $Adapter->get_balance( "ETH", array( 'type' => 'exchange' ) );
		$eth_bal = $eth_bal_arr['available'];

		$price_precision = 8;
		
		echo " -> eth balance ($eth_bal) \n";
		echo " -> btc balance ($btc_bal) \n";

		$buy_price = $eth_market['bid'];
		$sell_price = $eth_market['ask'];

		echo "buy price: $buy_price\n";
		echo "sell price: $sell_price\n";

		$epsilon = "0.00000005";

		//_____make 10 new visible orders:
		while( $sell_price - $buy_price > 0.00000001 ) {
			$buy_size = bcdiv( '0.0005', $buy_price, $price_precision );
			$sell_size = bcdiv( '0.0005', $sell_price, $price_precision );
			$buy_price = number_format( $buy_price, $price_precision, '.', '' );
			$sell_price = number_format( $sell_price, $price_precision, '.', '' );

			if( $buy_price < $sell_price ) {
				echo " -> btc bal $btc_bal before and eth bal $eth_bal before\n";

				if( $btc_bal > 0.0005 && $btc_bal > $buy_size * $buy_price ) {
					echo " -> buying $buy_size of ETH for $buy_price costing " . $buy_size * $buy_price . " \n";
					print_r( $Adapter->buy( $eth_market['market'], $buy_size, $buy_price, 'limit', array( 'market_id' => $eth_market['market_id'] ) ) );
					$btc_bal = $btc_bal - $buy_size * $buy_price;
				}
				if( $eth_bal > 0.02 && $eth_bal > $sell_size ) {
					echo " -> selling $sell_size of ETH for $sell_price costing " . $sell_size * $sell_price . " \n";
					print_r( $Adapter->sell( $eth_market['market'], $sell_size, $sell_price, 'limit', array( 'market_id' => $eth_market['market_id'] ) ) );
					$eth_bal = $eth_bal - $sell_size;
				}
				if( $btc_bal <= 0.0005 && $eth_bal <= 0.02 ) {

					$eth_market = $Adapter->get_market_summary( "BTC-ETH" );

					$btc_bal_arr = $Adapter->get_balance( "BTC", array( 'type' => 'exchange' ) );
					$btc_bal = $btc_bal_arr['available'];

					$eth_bal_arr = $Adapter->get_balance( "ETH", array( 'type' => 'exchange' ) );
					$eth_bal = $eth_bal_arr['available'];

					if( $btc_bal <= 0.0005 && $eth_bal <= 0.02 ) {
						exit;
					}
				}

			}

			$buy_price = $buy_price + $epsilon;
			$sell_price = $sell_price - $epsilon;

		}

		echo "\n";

	}

?>
