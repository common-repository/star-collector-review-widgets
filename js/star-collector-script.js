jQuery(document).ready(function ($) {
      
        $(".nav-tab").on("click", function () {
        // Get the data-tab attribute value
        var tabId = $(this).data("tab");

        // Show the selected tab content
        $(".tab-content").addClass("hide-class");
        $("#" + tabId).removeClass("hide-class");

        // Activate the clicked tab
        $(".nav-tab").removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
    });
        
        // check brand rating data
        const brandRatingData = pluginData.brandRatingData;
        if(brandRatingData){
            $(".star-collector-form-btn").removeClass('hide-class');
        }else{
            $(".star-collector-form-btn").addClass('hide-class');
        }
       
       
        $('#star-collector-form').submit(function () {
            // Prevent the default form submission
            event.preventDefault();

            // Get form data
            var formData = $(this).serialize();

            // AJAX request
            $.ajax({
                type: 'POST',
                url: ajaxurl, // WordPress AJAX handler
                data: {
                    action: 'SCRPlugin_fetch_data',
                    formData: formData
                },
                success: function (response) {
                    console.log(response);
                    var responseData = JSON.parse(response);
            //  console.log(responseData);

            // Check if there is an error in the response
            if (responseData.hasOwnProperty('error')) {
                // Handle error
                $('.start-collector-review').removeClass('hide-class');
                 $('#api-response').html('<span id="error" class="red">ERROR :' + responseData.error + '.</span>');
                $('#error').html('<span id="error" class="red">ERROR > ' + responseData.error + '.</span>');
                $('#star-collector-form-btn').prop('disabled', true);
                return;
            }
                $('#api-response').html('');
                const ratingArr = [];
                
               responseData.forEach(function (result, index) {
                // Create a checkbox element
                var checkbox = $('<input type="checkbox" style="margin-top: 0px;" class="ratingCheckbox">');
                checkbox.attr('name', 'rating_checkbox[]');
                checkbox.val(result.rating +'|'+ result.rating_out_of +'|'+ (result.review !== null ? result.review : result.votes) +'|'+ result.source+'|'+ result.favicon);
                
                // Create an image element for the favicon
               var faviconImg = $('<img>')
                    .attr('src', result.favicon)
                    .attr('alt', 'logo')
                    .css({
                        'width': '15px',
                        'margin-left': '5px',
                        'margin-right': '5px',
                    });


                // Create a label element
                var label = $('<label style="display: contents;">').text(' '+result.source + ' - Rating: ' + result.rating +'/'+result.rating_out_of);

                // Concatenate Reviews and Source (or Votes if Reviews is null) in a single line
                var reviewText = result.review !== null ? result.review : '' + result.votes;
                var lineText = ' - Reviews: ' + reviewText ;

                // Append the concatenated text to the label
                 label.append(' ' + lineText);
    
                // Append the checkbox and label to a container element (e.g., a div)
                var container = $('<div  style="margin-bottom: 10px; display:flex; align-items:center;">');
                container.append(checkbox).prepend(faviconImg);
                container.append(label);

                // Append the container to the API response element
                $('#api-response').append(container);
                
                var allRatingValues = (result.rating +'|'+ result.rating_out_of +'|'+ (result.review !== null ? result.review : result.votes) +'|'+ result.source+'|'+ result.favicon);
 
                ratingArr.push(allRatingValues);
                
                $('#ratingValues').val(ratingArr.join(', '));
                
                 // Get additional data from your input fields
                    var brandname = $('#brandname').val();
                    var identifiers = $('#identifiers').val();
                      var includePlatforms;
                     if($('#include-platforms').is(":checked")){
                          includePlatforms = 1;
                     }else{
                          includePlatforms = 0;
                     }
                    
                     // add data into brand database submit form
                    $('#getBrandname').val(brandname);
                    $('#getIdentifiers').val(identifiers);
                    $('#getIncludePlatforms').val(includePlatforms);
                    
                    // $('.start-collector-review').removeClass('hide-class');
                    $(".star-collector-form-btn").removeClass('hide-class')
                    $('#star-collector-form-btn').prop('disabled', true);
                
            });   
                    
                },
                  error: function (jqXHR, textStatus, errorThrown) {
                    // Handle AJAX request failure
                    console.error("AJAX request failed:", textStatus, errorThrown);
            
                    // You can add specific error handling logic here, such as displaying an error message to the user.
                    $('.start-collector-review').removeClass('hide-class');
                    $('#api-response').html('Failed to retrieve data. Please try again.');
                }
            });
        });

    
     
              var selectedCheckboxes = $('.ratingCheckbox:checked').length;
            
                // Enable or disable the form submission button based on the number of selected checkboxes
                if (selectedCheckboxes >= 1 && selectedCheckboxes <= 8) {
                    $('#star-collector-form-btn').prop('disabled', false);
                } else {
                    $('#star-collector-form-btn').prop('disabled', true);
                }
            
            // $('#star-collector-form-btn').prop('disabled', true);
            $(document).on('change', '.ratingCheckbox', function(e) {
               
                // Check the number of selected checkboxes
                selectedCheckboxes = $('.ratingCheckbox:checked').length;
            
                // Enable or disable the form submission button based on the number of selected checkboxes
                if (selectedCheckboxes >= 1 && selectedCheckboxes <= 8) {
                    $('#star-collector-form-btn').prop('disabled', false);
                } else {
                    $('#star-collector-form-btn').prop('disabled', true);
                }
                 
            });
            
            
        // pre set image
            const widgetPreview = $('#widget-preview');
            const widgetStylePreview = $('#widget-style-preview');
            const widgetStyleDesign = $('#widget-style-design');
            const imagesFolder = pluginData.imagesFolder;
            const displayPosition = pluginData.widgetDisplayPosition || '3';
            const displayMood = pluginData.widgetDisplayMood || '1';
            const displayStyle = pluginData.widgetDisplayStyle || '1';
        
            // Set the default image source based on the value from the database
            const imagePathPosition = imagesFolder + 'widget-preview-img-' + displayPosition + '.png';
            const imagePathMode = imagesFolder + 'widget-style-preview-' + displayMood + '.png';
            const imagePathStyle = imagesFolder + 'widget-style-design-' + displayStyle + '.png';
        
            widgetPreview.attr({
                'src': imagePathPosition,
            });
            
             widgetStylePreview.attr({
                'src': imagePathMode,
            });
            
             widgetStyleDesign.attr({
                'src': imagePathStyle,
            });
        
        // radio button value
         $("input[name='display_position']").click(function() {
            const selectedValue = $(this).val();
            const displayPosition = selectedValue || '3';
            const imagePathPosition = imagesFolder + 'widget-preview-img-' + displayPosition + '.png';
    
            widgetPreview.attr({
                'src': imagePathPosition,
            });
            
            // style display
            var displayStyle = pluginData.widgetDisplayStyle;
            
            if(selectedValue == 1 || selectedValue == 2){
                displayStyleButtons = {
                '1': 'Style A',
                '2': 'Style B',
                '3': 'Style C',
                '4': 'Style D',
                '5': 'Style E',
                '6': 'Style F',
                '7': 'Style G',
            };
            }else{
                 displayStyleButtons = {
                '1': 'Style A',
                '2': 'Style B',
                '3': 'Style C',
                '4': 'Style D',
                '5': 'Style E',
                '6': 'Style F',
                '7': 'Style G',
            };
            }
            
            // Update display_style radio buttons dynamically
    var displayStyleButtonsHtml = '';
    var disabled;
    $.each(displayStyleButtons, function (value, label) {
        if(selectedValue == 1 || selectedValue == 2){
             if(value != 7){
                 disabled = 'disabled';
                 value = '';
            }else{
                disabled = '';
                value;
            }
        }else{
             if(value == 7){
                disabled = 'disabled';
                value = '';
            }else{
                disabled = '';
                value;
            }
        }
        displayStyleButtonsHtml += '<label>';
        displayStyleButtonsHtml += '<input type="radio" name="display_style" value="' + value + '" ' + (displayStyle == value ? 'checked' : '') + ' required '+ disabled +'> ';
        displayStyleButtonsHtml += label;
        displayStyleButtonsHtml += '</label>';
        displayStyleButtonsHtml += '<br>';
    });

    $('.displayStyleButtons').html(displayStyleButtonsHtml);
              
         });
         
        //  radio for display mood
          $("input[name='display_mode']").click(function() {
            const selectedValue = $(this).val();
            const displayMode = selectedValue || '1';
            const imagePathMode = imagesFolder + 'widget-style-preview-' + displayMode + '.png';
    
            widgetStylePreview.attr({
                'src': imagePathMode,
            });
         });
         
        //  specific_url field
         const specificUrlCheckbox = $("input[name='specific_url_checkbox']").is(":checked");
         if(specificUrlCheckbox == false){
             $("textarea[name='specific_url']").prop('readonly', true);
         }else{
             $("textarea[name='specific_url']").prop('readonly', false);
         }
         
           $("input[name='specific_url_checkbox']").click(function() {
               var getCheckBoxVAL = $("input[name='specific_url_checkbox']").is(":checked");
               if(getCheckBoxVAL == false){
             $("textarea[name='specific_url']").prop('readonly', true);
             $("textarea[name='specific_url']").val(null);
         }else{
             $("textarea[name='specific_url']").prop('readonly', false);
         }
         });
         
        //  radio button for display style
         $(document).on('click', 'input[name="display_style"]', function(e) {
            const selectedValue = $(this).val();
            const displayStyle = selectedValue || '1';
            const imagePathStyle = imagesFolder + 'widget-style-design-' + displayStyle + '.png';
            let _class;  
            if(selectedValue == 7) {
                _class = 'widget-display-styleG-img';
            }
            else {
                _class = 'widget-display-style-img';
            }
    
            widgetStyleDesign.attr({
                'src': imagePathStyle,
                //'class': _class,
            });
        });
        
        $('.requiredCheckbox').on('change', function(e) {
        // Check the number of selected checkboxes
                const selectedCheckboxes = $('.requiredCheckbox:checked').length;
            
                // Enable or disable the form submission button based on the number of selected checkboxes
                if (selectedCheckboxes >= 1) {
                    $('.requiredCheckbox').prop('required', false);
                } else {
                     $('.requiredCheckbox').prop('required', true);
                }
                });
                
                
                // trustworthiness data save or update in DB
                $('.reviewise-badge').on('change', function () {
                var rewiseBadge = $(this).prop('checked') ? 1 : 0;
                var trustworthinessId = pluginData.trustworthinessId;
                
                $.ajax({
                    type: 'POST',
                    url: pluginData.ajax_url,
                    data: {
                        action: 'SCRPlugin_update_trustworthiness',
                        trustworthinessId: trustworthinessId,
                        rewiseBadge: rewiseBadge,
                    },
                    success: function (response) {
                        // Trigger an event or callback function here
                        $(document).trigger('trustworthinessUpdated', [rewiseBadge]);
                        if(rewiseBadge == 1){
                            $('#error2').html('<span id="error2" class="green-star"> YES > All Good!<span>');
                        }else{
                            $('#error2').html('<span id="error2" class="red"> NO > Enable to Enhance Credibility and Trustworthiness.<span>');
                        }
                    },
                    error: function (error) {
                        // Handle the error if needed
                        console.error(error);
                    }
                });
            });
            
            $(document).on('trustworthinessUpdated', function (event, rewiseBadge) {
            // Update the checkbox state in other areas
            $('.reviewise-badge').prop('checked', rewiseBadge);
        });
        
});