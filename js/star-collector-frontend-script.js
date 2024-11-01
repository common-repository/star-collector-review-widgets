function onload(event) {
        
        var averageRating = ratingData.averageRating;
        var star = ratingData.star;
         var font = ratingData.font;
	 var myDataService =  {
		 rate:function(rating) {
				return {then:function (callback) {
					setTimeout(function () {
						callback((Math.random() * 5)); 
					}, 1000); 
				}
			}
		}
	}
	
	 if(star == 1){
        $rating = averageRating/5;
        }else{
            $rating = averageRating*1;
        }
	var starRating1 = raterJs( {
	    max:star,
		starSize:font,
		rating: $rating, 
		readOnly:true,
		showToolTip:false,
		element:document.querySelector("#rater"), 
		rateCallback:function rateCallback(rating, done) {
			this.setRating(rating); 
			done(); 
		}
	}); 
	
    
}

window.addEventListener("load", onload, false); 
