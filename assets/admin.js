/** global CGRResults **/
jQuery(function($) {
	if( undefined == CGRResults ){
		return;
	}
	console.log( CGRResults );
	if( 'object' === typeof  CGRResults ){

		function checkResultUrl( resultUrl, i ) {
			var elId = 'cgi-' + CGRResults.testIds[i];
			var el, $el;
			var setEl = function () {
				el = document.getElementById(elId);
				if( null !== el ){
					$el = $( el );
				}else{
					$el = $( '<div />', { id: elId } ).appendTo( '#ghost-runner' );
				}

				$el.html( CGRResults.testIds[i] );


			};

			var timeOut;

			var times = -1;

			var running = false;


			var check = function ( ) {
				return $.ajax({
					url: resultUrl
				}).then( function( d ){
					return d;
				});
			};
			
			var doCheck = function () {
				if( running ){
					return;
				}

				function rerun() {
					$el.css(
						{ "border": "0px solid grey" }
					).animate({
						opacity: 1.0,
						color: "grey",
						borderWidth: 4
					}, 250, function() {
						timeOut = setTimeout( function(){ doCheck() }, 1000 );
					});
				}


				if( 0 > times ){
					times = 1;
					rerun();
					return;
				}


				$.when( check() ).then( function ( d ) {
					if ( d.incomplete ) {
						running = false;
						$( '<div />', { 'class': 'incomplete' } ).appendTo( $el ).html( 'Incomplete' ).css( 'color', 'yellow' );
						$el.css(
							{ "border": "0px solid yellow" }
						).animate({
							opacity: 0.75,
							color: "yellow",
							borderWidth: 4
						}, 200, function() {
							timeOut = setTimeout( function(){ doCheck() }, 1000 );
						});
					}else{
						$el.find( '.incomplete' ).remove();
						var css = {
							display: 'inline',
							margin: '4px'
						};

						$( '<a />', {
							href: d.testUrl,
							class: 'testUrl',
							target: '_blank'
						})
							.appendTo( $el ).html( 'Test URL' )
							.css( css );

						$( '<a />', {
							href: d.formUrl,
							class: 'formUrl',
							target: '_blank'
						})
							.appendTo( $el ).html( 'Form URL' )
							.css( css );


						$( '<a />', {
							href: d.videoUrl,
							class: 'videoUrl',
							target: '_blank'
						})
							.appendTo( $el ).html( 'Video URL' )
							.css( css );


						if( d.passing ){
							$el.find( '.incomplete' ).remove();
							$( '<div />', { 'class': 'passed' } ).appendTo( $el ).html( 'Passed' ).css( 'color', 'green');

							clearTimeout( timeOut );
							$el.css(
								{ "border": "0px solid green" }
							).animate({
								opacity: 1.0,
								color: "green",
								borderWidth: 4
							}, 1500, function() {
								$el.css({
									color:"green",
									borderWidth: 1
								})
							});
						} else {
								$( '<div />', { 'class': 'passed' } ).appendTo( $el ).html( 'Not Passed' ).css( 'color', 'red');

								$el.animate({
									opacity: 0.75,
									color: "red",
									borderWidth: 4
								}, 200, function() {
									$el.css( 'border', '1px solid red' );
								});

							}
						}

				});
			};

			setEl();


			doCheck();


			




		}

		if( CGRResults.hasOwnProperty( 'resultUrls' ) ){
			CGRResults.resultUrls.forEach( function( resultUrl, i ){
				checkResultUrl( resultUrl, i );
			});
		}
	}
});