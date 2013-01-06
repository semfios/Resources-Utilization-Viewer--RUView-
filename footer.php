		<style>
			#years-nav{
				font-size: 30px;
				font-weight: bold;
				width: 300px;
				border: dotted 1px silver;
				text-align: center;
				margin: 0 auto;
			}
			#years-nav a:hover{
				text-decoration: underline;
				background-color: Gold;
			}
			#years-nav a{
				font-weight: normal;
				font-size: 20px;
				margin: 0 30px;
				text-decoration: none;
				color: DarkBlue;
			}
		</style>
		
		<script>
			document.observe("dom:loaded", function() {
				/* Add link for resource utilization chart into the nav menu */
				var nmc = $$('select[name=nav_menu]')[0].options.length;
				var chart = new Option("Chart", "chart.php");

				$$('select[name=nav_menu]')[0].options[nmc] = chart;
			});
		</script>
		
		<!-- Add footer template above here -->
		</body>
	</html>