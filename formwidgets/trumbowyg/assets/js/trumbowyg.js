/*
 * Rich text editor form field control (WYSIWYG)
 *
 * Data attributes:
 * - data-control="richeditor" - enables the rich editor plugin
 *
 * JavaScript API:
 * $('textarea').richEditor()
 *
 * Dependancies:
 * - Trumbowyg - http://alex-d.github.io/Trumbowyg/#home
 */
+function ($) { "use strict";

    // Initialize
    // ===============
    $(document).render(function () {

    	var slug;
    	if($('#Form-field-Template-slug').val()){
    		slug = $('#Form-field-Template-slug').val();
    	}else{
    		slug = $('.nav.nav-tabs li.active:first a').attr('title');
    	}

    	$.extend(true, $.trumbowyg, {
                upload: {
		            serverPath: window.location.href.toString().split(window.location.host)[0] +
                                window.location.host.toString() +
                                '/octodevel/octomail/upload/' + slug.toLowerCase() + '/'
		        }
            });
        $('[data-control="trumbowyg"] textarea').trumbowyg({
        		fixedBtnPane: true,
         		autogrow: true,
                btnsDef: {
                    // Customizables dropdowns
                    align: {
                        dropdown: ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                        ico: 'justifyLeft'
                    },
                    image: {
                        dropdown: ['insertImage', 'upload'],
                        ico: 'insertImage'
                    }
                },
                btns: ['viewHTML',
                '|', 'formatting',
                '|', 'bold',
                 	 'italic',
                 	 'underline',
                 	 'strikethrough',
                '|', 'link',
                '|', 'image',
                '|', $.trumbowyg.btnsGrps.justify,
                '|', $.trumbowyg.btnsGrps.lists,
                '|', 'horizontalRule']
            });

    });

}(window.jQuery);
