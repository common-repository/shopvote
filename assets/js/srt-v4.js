var loadSRT = function(token, Src) {
	var delay = 1000;

	if (document.getElementById('srt-customer-data')
		&& document.getElementById('srt-customer-email') && (document.getElementById('srt-customer-email').innerHTML != 'CUSTOMERMAIL')
		&& document.getElementById('srt-customer-reference') && (document.getElementById('srt-customer-reference').innerHTML != 'ORDERNUMBER')
		&& (document.getElementById('srt-customer-reference').innerHTML.length > 1)
	) {
		var srt = function srt() {
			if (typeof(myLanguage) == "undefined") {
				myLanguage = "de";
			} else {
				myLanguage = myLanguage.toLowerCase();
			}

			var link      = document.createElement("link"),
				div       = document.createElement("div"),
				request   = "https://feedback.shopvote.de/popup-reviews.php?token=" + token + "&src=" + Src + "&lang=" + myLanguage;

			link.href = "https://feedback.shopvote.de/request.min.css";
			link.type = "text/css";
			link.rel = "stylesheet";
			link.media = "screen,print";

			div.setAttribute("id","shopvote-rating-tool");
			document.getElementsByTagName("head")[0].appendChild(link);

			if (XMLHttpRequest) {
				var x = new XMLHttpRequest();
			} else {
				var x = new ActiveXObject("Microsoft.XMLHTTP");
			}

			x.open("GET", request, true);
			x.send();
			x.onreadystatechange = function() {
				if (x.readyState !== 4) {
					return;
				}
				if (x.status == 400) console.log('Error[400]: Bad Request');
				if (x.status == 451) console.log('Error[451]: Bad API-Key');
				if (x.status == 452) console.log('Error[452]: Function not active or missing payment');
				if (x.status == 454) console.log('Error[454]: Request not from accepted CheckoutURL; See SHOPVOTE-Backend configuration of this function');
				if (x.status == 204) console.log('Error[204]: Popup normal blocked - Consent or rejection already chosen');
				if (x.status != 200) {
					div.style.display = 'none';
					return;
				}
				var tableCells = document.querySelectorAll('.CustomerInfo table td');
				if (tableCells.length > 0) {
					var ePagesMail = tableCells[4].innerHTML;
				}
				document.body.appendChild(div);
				div.innerHTML = x.responseText;
				if (document.getElementById("srt-customer-data")) {
					var data  = document.getElementById("srt-customer-data").getElementsByTagName("span");
					for(var i = 0, l = data.length; i < l; i++) {
						var formData      = document.getElementById(data[i].id.replace("customer", "form")),
							customerData  = document.getElementById(data[i].id).innerText,
							srtWindow     = document.getElementById("shopvote-rating-tool"),
							mailField;
						formData.value = customerData;
						formData.name = document.getElementById(data[i].id).id.replace("customer", "form");
						if (typeof ePagesMail !== "undefined") {
							mailField = document.getElementById('srt-form-email');
							mailField.value = ePagesMail;
						}
					}
				}
			}
		}
		setTimeout(srt, delay);
	}
}

function stopPopup(shopid) {
	let request = "https://feedback.shopvote.de/popup-stop.php?shopid="+shopid;
	if (XMLHttpRequest) {
		var x = new XMLHttpRequest();
	} else {
		var x = new ActiveXObject("Microsoft.XMLHTTP");
	}
	x.open("GET", request, true);
	x.send();
	x.onreadystatechange = function() {
		var el = document.getElementById("shopvote-rating-tool");
		if (!el || (x.readyState != 4) || (x.status != 200)) {
			return;
		}
		el.parentNode.removeChild(el);
	}
}

function acceptRequest(shopid) {
	var postData 	= "srt-form-token=" + document.getElementById("srt-form-token").value + "&" +
					  "srt-form-authtoken=" + document.getElementById("srt-form-authtoken").value + "&" +
					  "srt-form-checksum=" + document.getElementById("srt-form-checksum").value + "&" +
					  "srt-form-ptoken=" + document.getElementById("srt-form-ptoken").value + "&" +
					  "srt-form-ip=" + document.getElementById("srt-form-ip").value + "&" +
					  "srt-form-lang=" + document.getElementById("srt-form-lang").value + "&" + 
					  "srt-form-captcha=" + document.getElementById("srt-form-captcha").value + "&" +
					  "srt-form-reference=" + document.getElementById("srt-form-reference").value + "&" +
					  "srt-form-email=" + document.getElementById("srt-form-email").value;

	// Prüfen, ob Produkte vorhanden
	if ((typeof shopvote_order_products === 'object') && (shopvote_order_products.length > 0)) {
		//convert object to json string
		var Products = shopvote_order_products;
		postData = postData + "&srt-form-products=" + encodeURIComponent(JSON.stringify({Products}));

		// oder ist Element mit ID "SHOPVOTECheckoutProducts" vorhanden
	} else if ((document.getElementById('SHOPVOTECheckoutProducts')) && (document.getElementsByClassName("SVCheckoutProductItem"))) { // Element vorhanden
		var Products = [];
		var items = document.getElementsByClassName("SVCheckoutProductItem"); // Alle Produkte holen
		var i;
		for (i = 0; i < items.length; i++) {
			var Product = {};
			var itemElement = document.getElementsByClassName("SVCheckoutProductItem")[i];
			if (itemElement.getElementsByClassName("sv-i-product-url").length > 0) {
				Product['URL'] = itemElement.getElementsByClassName("sv-i-product-url")[0].innerText;
			}
			if (itemElement.getElementsByClassName("sv-i-product-image-url").length > 0) {
				Product['ImageURL'] = itemElement.getElementsByClassName("sv-i-product-image-url")[0].innerText;
			}
			if (itemElement.getElementsByClassName("sv-i-product-name").length > 0) {
				Product['Product'] = itemElement.getElementsByClassName("sv-i-product-name")[0].innerText;
			}
			if (itemElement.getElementsByClassName("sv-i-product-gtin").length > 0) {
				Product['GTIN'] = itemElement.getElementsByClassName("sv-i-product-gtin")[0].innerText;
			}
			if (itemElement.getElementsByClassName("sv-i-product-sku").length > 0) {
				Product['SKU'] = itemElement.getElementsByClassName("sv-i-product-sku")[0].innerText;
			}
			if (itemElement.getElementsByClassName("sv-i-product-brand").length > 0) {
				Product['Brand'] = itemElement.getElementsByClassName("sv-i-product-brand")[0].innerText;
			}
			Products.push(Product);
		}

		//convert object to json string
		var string = JSON.stringify({Products});

		// convert string to Json Object
		// console.log("Erfasste Produkte: " + string); // this is your requirement.
		postData = postData + "&srt-form-products=" + encodeURIComponent(string);
		// console.log("postData: " + postData);
	}

	var xmlHttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

	if (xmlHttp != null && document.getElementById("srt-form-authtoken").value.length >= 3) {
		xmlHttp.open( 'POST', 'https://feedback.shopvote.de/review-request-products.php', true );
		xmlHttp.onreadystatechange = function () {
			if( xmlHttp.readyState == 4 && xmlHttp.status == 200)  {
				if (xmlHttp.responseText == "0") {
					stopPopup(shopid);
				} else {
					stopPopup(shopid);
				}
			} else {
				stopPopup(shopid);
			}
			//console.log("Response: " + xmlHttp.responseText);
		};
		xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xmlHttp.setRequestHeader('Access-Control-Allow-Origin', '*');

		xmlHttp.send(postData);
	} else {
		stopPopup(shopid);
		console.log("Bad Auth-Token");
	}
}

function checkForm() {
	if (document.getElementById("srt-form-email")) {
		var x = getElementById("srt-form-email").value;
		if (x == null || x == "") {
			getElementById("srt-form-email").style.border = "thin solid red";
		} else {
			if(document.getElementById("shopvote-rating-tool")) {
				var el = document.getElementById("shopvote-rating-tool");
				el.parentNode.removeChild(el);
			}
		}
	}
}

function transmitToken(token) {
	document.getElementById("srt-shop-token").value = token;
}
