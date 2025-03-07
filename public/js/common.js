$(function() {

	$("nav").find('.toggle-mnu').click(function () {
		$(this).toggleClass("on");
		$("nav ul").slideToggle().toggleClass("active");
		$("body").toggleClass("active");
		return false;
	});

	//magnific
	$("a[href='#callback']").magnificPopup({
		mainClass: 'my-mfp-zoom-in',
		removalDelay: 300,
		midClick: true,
		closeBtnInside:true,
		fixedContentPos: false,
		type: 'inline',
		focus: '#name',
		callbacks: {
			beforeOpen: function() {
				jQuery('body').css('overflow', 'hidden');
			},
			beforeClose: function() {
				jQuery('body').css('overflow', 'auto');
			}
		}
	});  

	//owl-carousel
	$('.owl-slider').owlCarousel({
		loop: true,
		smartSpeed: 700,
		margin: 0,
		autoplay: true,
		autoplayTimeout: 9000,
		nav: false,
		dots: true,
		//animateOut: 'fadeOut',
		//animateIn: 'fadeIn',
		responsiveClass: true,
		responsive: {
			0: {
				items: 1
			},
			1200: {
				items: 1
			}
		}
	});


	AOS.init();

	$(".gallery").each(function () {
		$(this).magnificPopup({
			delegate: 'a',
			mainClass: 'mfp-zoom-in',
			type: 'image',
			tLoading: '',
			gallery: {
				enabled: true,
			},
			removalDelay: 300,
			callbacks: {
				beforeChange: function () {
					this.items[0].src = this.items[0].src + '?=' + Math.random();
				},
				open: function () {
					$.magnificPopup.instance.next = function () {
						var self = this;
						self.wrap.removeClass('mfp-image-loaded');
						setTimeout(function () { $.magnificPopup.proto.next.call(self); }, 120);
					}
					$.magnificPopup.instance.prev = function () {
						var self = this;
						self.wrap.removeClass('mfp-image-loaded');
						setTimeout(function () { $.magnificPopup.proto.prev.call(self); }, 120);
					}
				},
				imageLoadComplete: function () {
					var self = this;
					setTimeout(function () { self.wrap.addClass('mfp-image-loaded'); }, 16);
				}
			}
		});
	});

	$(document).ready(function () {
		//equal
		function heightses() {
			$(".service h4").height("auto").equalHeights();
			$(".adv-item").height("auto").equalHeights();
		} heightses();

		$(window).resize(function () {
			heightses();
		});
	});

	$('ul.tabs li').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	});

	const accordionBtns = document.querySelectorAll(".accordion");

	accordionBtns.forEach((accordion) => {
		accordion.onclick = function () {
			this.classList.toggle("is-open");

			let content = this.nextElementSibling;
			//console.log(content);

			if (content.style.maxHeight) {
				//this is if the accordion is open
				content.style.maxHeight = null;
			} else {
				//if the accordion is currently closed
				content.style.maxHeight = content.scrollHeight + "px";
				//console.log(content.style.maxHeight);
			}
		};
	});

	

});
