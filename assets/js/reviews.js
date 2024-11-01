var review = {};
//document.querySelector('#main').style.opacity = 0
//if (document.querySelector('.related') != null) {
//    const relatedProdRating = document.querySelectorAll('.related .products .entry .star-rating')
//    if (relatedProdRating.length > 0) {
//        relatedProdRating.forEach(el => {
//            el.remove()
//        })
//    }          
//}
function shopvote_fetch_reviews(sku) {
    let reviews = []
    jQuery.ajax({
        url: shopvote_ajaxurl,
        type: 'POST',
        dataType: 'json',
        async: false,
        cache: false,
        data: {
            action: 'shopvote_get_reviews',
            sku: encodeURIComponent(sku)
        },
        success: function(data, status) {
            if ('status' in data && data.status === 'success') {
                reviews = data.reviews.reviews
            }
        }
    })
    return reviews;
}

function svRating(reviews) {
    try {
        review = reviews;
        if (review.Code != '401' &&
            review.reviews.length > 0) {
                var paymentButtonDiv = document.querySelectorAll("form.cart");
                var d = document;
                if (paymentButtonDiv.length) {
                    if (document.querySelector('.woocommerce-product-rating') != null && review.reviews.length > 0) {
                        const starRating = document.querySelector('.star-rating') != null ? document.querySelector('.star-rating .rating').innerHTML : 0
                        const totalRating = (parseInt(starRating) + parseInt(review.rating_value)) / 2
                        const totalRatingPerc = totalRating * 20
                        const starsPerc = document.querySelector('.star-rating >span:first-child')
                        starsPerc.removeAttribute('style')
                        starsPerc.setAttribute('style', 'width:' + totalRatingPerc + '%')
                    }
                    shopvote_setJsonLD(review);
                }
            }
    } catch (error) {
        console.log(error);
    }
}

function shopvote_addReview(single) {
    return new Promise(function(resolve) {
        resolve(`{
            "@type": "Review",
            "author": "`+single.author+`",
            "datePublished": "`+single.created+`",
            "reviewBody": "`+escape(single.text)+`",
            "reviewRating": {
                "@type": "Rating","bestRating": "5","ratingValue": "`+single.rating_value+`", "worstRating": "1"
            }
        }`);
    });
}

function shopvote_setJsonLD(review) {
    try {
        var promises = [];
        if (review.reviews.length > 0) {
            for (i=0; i<review.reviews.length; i++) {
                promises.push(shopvote_addReview(review.reviews[i]));
            }
        }

        Promise.all(promises).then(function(string) {
            var object = `{
                "@context": "http://schema.org",
                "@type": "Product",
                "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "`+review.rating_value+`",
                    "reviewCount": "`+review.rating_count+`"
                    },
                    "name": "`+escape(review.productname)+`",
                    "review": [`+string+`]
                }
            }`;

            var head = document.getElementsByTagName('head')[0];
            var json = document.createElement("script");
                json.setAttribute('type', 'application/ld+json');
                json.text = object;

            head.append(json);
        });

    } catch (error) {
        console.log(error);
    }

}
