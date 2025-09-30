$(function () {

	$(window).on('load', function () {
		$('#preloader').fadeOut('slow');
	});

	$("nav").find('.toggle-mnu').click(function () {
		$(this).toggleClass("on");
		$("nav ul").slideToggle().toggleClass("active");
		$("body").toggleClass("active");
		return false;
	});

	//lang
	$('#lang').click(function (e) {
		e.stopPropagation(); // Prevent click from bubbling to document
		$('#over').addClass('active');
	});

	$(document).click(function () {
		$('#over').removeClass('active');
	});

	//count
	$('#count_person').click(function () {
		$('#count-wrap').addClass('active');
	});

	//filter
	$('#filter').click(function () {
		$('#filter-wrap').addClass('active');
	});

	$('#closebtn').click(function (e) {
		e.stopPropagation(); // Prevents triggering #filter click again
		$('#filter-wrap').removeClass('active');
	});


	$('#filter .item').find('input').click(function () {
		$('.item').find('.img').removeClass('active');
		if ($(this).is(":checked")) {
			$(this).parent().find('.img').toggleClass('active');
		}
		$(this).parent().find('input.type').prop("checked", true);
	});

	$('#filter #income').find('input').click(function () {
		$('.itemm').removeClass('active');
		if ($(this).is(":checked")) {
			$(this).parent().toggleClass('active');
		}
		$(this).parent().find('input.type').prop("checked", true);
	});

	$('#filter #meal input[type="checkbox"]').on('change', function () {
		if ($(this).is(':checked')) {
			$(this).parent().addClass('active');
		} else {
			$(this).parent().removeClass('active');
		}
	});


	//fixed menu
	$(window).scroll(function () {
		if ($(this).scrollTop() > 100) {
			$('header').fadeIn().addClass("fixed");
		} else {
			$('header').removeClass("fixed").animate('slow');
		}
	});

	//magnific
	$("a[href='#callback']").magnificPopup({
		mainClass: 'my-mfp-zoom-in',
		removalDelay: 300,
		midClick: true,
		closeBtnInside: true,
		fixedContentPos: false,
		type: 'inline',
		focus: '#name',
		callbacks: {
			beforeOpen: function () {
				jQuery('body').css('overflow', 'hidden');
			},
			beforeClose: function () {
				jQuery('body').css('overflow', 'auto');
			}
		}
	});

	$(".book-item").magnificPopup({ mainClass: "my-mfp-zoom-in", removalDelay: 300, type: "inline" });

	$(".book-item").each(function (t) {
		$(this)
			.attr("href", "#book-" + t)
			.find(".book-popup")
			.attr("id", "book-" + t);
	});


	//owl-carousel
	$('.owl-vantages').owlCarousel({
		loop: true,
		smartSpeed: 700,
		margin: 20,
		autoplay: true,
		//center: true,
		autoplayTimeout: 4000,
		nav: true,
		navText: ["<img src='img/arrow-left.svg'>","<img src='img/arrow-right.svg'>"],
		dots: true,
		//animateOut: 'fadeOut',
		//animateIn: 'fadeIn',
		responsiveClass: true,
		responsive: {
			0: {
				items: 1.2,
				nav: false
			},
			1200: {
				items: 3
			}
		}
	});

	$('.owl-partners').owlCarousel({
		loop: true,
		smartSpeed: 700,
		margin: 20,
		autoplay: true,
		//center: true,
		autoplayTimeout: 4000,
		nav: true,
		navText: ["<img src='img/arrow-left.svg'>","<img src='img/arrow-right.svg'>"],
		dots: true,
		//animateOut: 'fadeOut',
		//animateIn: 'fadeIn',
		responsiveClass: true,
		responsive: {
			0: {
				items: 1.2,
				nav: false
			},
			768: {
				items: 2
			},
			1200: {
				items: 4
			}
		}
	});

	$('.owl-tariffs').owlCarousel({
		loop: false,
		smartSpeed: 700,
		margin: 10,
		autoplay: false,
		//center: true,
		autoplayTimeout: 900000,
		nav: true,
		navText: ["<img src='img/arrow-left.svg'>","<img src='img/arrow-right.svg'>"],
		dots: false,
		//animateOut: 'fadeOut',
		//animateIn: 'fadeIn',
		responsiveClass: true,
		responsive: {
			0: {
				items: 2.2
			},
			767: {
				items: 2
			},
			1200: {
				items: 3
			}
		}
	});

	$('.owl-slider').owlCarousel({
		loop: false,
		smartSpeed: 700,
		margin: 10,
		autoplay: false,
		//center: true,
		autoplayTimeout: 900000,
		nav: false,
		dots: true,
		//animateOut: 'fadeOut',
		//animateIn: 'fadeIn',
		responsiveClass: true,
		responsive: {
			0: {
				items: 1
			},
			767: {
				items: 1
			},
			1200: {
				items: 1
			}
		}
	});

	AOS.init();

	$('ul.tabs li').click(function () {
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#" + tab_id).addClass('current');
	});



	const input = document.querySelector("#phone");
	const output = document.querySelector(".output");

	const iti = window.intlTelInput(input, {
		nationalMode: false,
		initialCountry: 'kg',
		utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
	});

	const handleChange = () => {
		let text;
		if (input.value) {
			text = iti.isValidNumber()
				? "Действительный номер " + iti.getNumber()
				: "Неверный номер - попробуйте еще раз";
		} else {
			text = "Пожалуйста, введите действительный номер";
		}
		if (iti.isValidNumber()) {
			output.classList.add("agree");
			//document.getElementById("send").disabled = false;
		} else {
			output.classList.remove("agree");
			//document.getElementById("send").disabled = true;
		}
		const textNode = document.createTextNode(text);
		output.innerHTML = "";
		output.appendChild(textNode);
	};

	input.addEventListener('change', handleChange);
	input.addEventListener('keyup', handleChange);



});
