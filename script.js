document.addEventListener("DOMContentLoaded", function () {
    jQuery(function ($) {
        // --------------------------------
        // Mausverfolgung für Buttons
        // --------------------------------
        document.querySelectorAll(".fill-top-white, .fill-top, .et_pb_contact_submit, .spa--grid a.more-link").forEach((button) => {
            let isAnimating = false;
            button.addEventListener("mousemove", function (e) {
                if (!isAnimating) {
                    isAnimating = true;
                    requestAnimationFrame(() => {
                        let rect = this.getBoundingClientRect();
                        let mouseX = ((e.clientX - rect.left) / rect.width) * 100;
                        this.style.setProperty("--mouse-x", `${mouseX}%`);
                        isAnimating = false;
                    });
                }
            });
        });




        // --------------------------------
        // RIPPLE (Fix für passive Events)
        // --------------------------------
        $(document).ready(() => {
            let $el = $(".ripple");
            if (!$el.length) return;

            $el.ripples({
                resolution: 512,
                dropRadius: 25,
                interactive: true,
                perturbance: 0.01
            });

            // Fix für passive Touch-Events
            $el.each((_, el) => {
                el.addEventListener("touchstart", () => {}, { passive: true });
                el.addEventListener("touchmove", () => {}, { passive: true });
            });
        });



        // --------------------------------
        // Hover Titel verstecken
        // --------------------------------
        $(document).on("mouseenter mouseleave", "img", function (event) {
            let $this = $(this);
            if (event.type === "mouseenter") {
                $this.data("lwp_title", this.title).attr("title", "");
            } else {
                $this.attr("title", $this.data("lwp_title"));
            }
        });

        // --------------------------------
        // Autoplay für Videos
        // --------------------------------
        $(".autoplay-video .et_pb_video_box video")
            .prop({ muted: true, loop: true, playsInline: true })
            .removeAttr("controls")
            .each(function () { this.play(); });


        // --------------------------------
        // Textanimierung für speziellerText (Optimiert)
        // --------------------------------
        function revealSpans() {
            let viewportHeight = window.innerHeight;
            let elements = document.querySelectorAll(".speziellerText");
            let updates = [];

            elements.forEach(element => {
                if (!element.dataset.converted) {
                    element.innerHTML = [...element.textContent].map(char => `<span>${char}</span>`).join("");
                    element.dataset.converted = "true";
                }

                element.querySelectorAll("span").forEach(span => {
                    let rect = span.getBoundingClientRect();
                    let opacityValue = 1 - ((rect.top - viewportHeight * 0.6) * 0.01 + rect.left * 0.001);
                    updates.push({ span, opacity: Math.max(0.01, Math.min(1, opacityValue)) });
                });
            });

            // **Alle DOM-Updates in einem Schritt ausführen**
            requestAnimationFrame(() => {
                updates.forEach(({ span, opacity }) => {
                    span.style.opacity = opacity;
                });
            });
        }

        // **Event-Listener (keine Dopplung!)**
        window.addEventListener("scroll", () => {
            requestAnimationFrame(revealSpans);
        });

        // **Direkt beim Laden einmal ausführen**
        revealSpans();


        

        // --------------------------------
        // Typing-Effekt für Haupt- & Unterseiten
        // --------------------------------
        function setTyper(element, words, typeSpeed = 100, deleteSpeed = 50, delayBetweenWords = 2000) {
            let wordIndex = 0, letterIndex = 0, direction = 1;

            function type() {
                let word = words[wordIndex];
                element.textContent = word.substring(0, letterIndex);

                if (direction === 1) {
                    letterIndex++;
                    if (letterIndex > word.length) {
                        direction = -1;
                        setTimeout(type, delayBetweenWords);
                        return;
                    }
                } else {
                    letterIndex--;
                    if (letterIndex === 0) {
                        direction = 1;
                        wordIndex = (wordIndex + 1) % words.length;
                    }
                }
                setTimeout(type, direction === 1 ? typeSpeed : deleteSpeed);
            }
            type();
        }

        // --------------------------------
        // Automatische Initialisierung für alle .typed-text-Elemente mit "data-type"
        // --------------------------------
        document.querySelectorAll(".typed-text").forEach((element) => {
            let words = [];
            let type = element.getAttribute("data-type"); // Liest das "data-type" aus

            switch (type) {
                case "hauptseite":
                    words = [
                        "Erlebnisbad",
                        "Sauna-Paradies",
                        "Spa-Bereich",
                        "kulinarisches Erlebnis",
                        "Wohnmobilstellplatz"
                    ];
                    break;
                case "erlebnisbad":
                    words = [
                        "Erlebnisbad",
                        "Wasserspaß",
                        "Rutschrekord",
                        "Schwimmbad",
                        "Sprungturm",
                        "Spielspaß",
                        "Kursbecken"
                    ];
                    break;
                case "spa":
                    words = [
                        "Spa-Verwöhnung",
                        "Wellness-Oase",
                        "Entspannungskur",
                        "Massage",
                        "Beauty-sphäre"
                    ];
                    break;
                case "sauna":
                    words = [
                        "Sauna-Paradies",
                        "Sauna-Erlebnis",
                        "Banja-Ritual",
                        "Schwitze-Moment",
                        "Sanarium",
                        "Dampfbad"
                    ];
                    break;
                case "gastro":
                    words = [
                        "kulinarischen Erlebnisse",
                        "leckeren Snacks",
                        "frischen Zutaten",
                        "Morgenkaffees",
                        "regionalen Sattmacher"
                    ];
                    break;
                default:
                    words = [
                        "Erlebnisbad",
                        "Sauna-Paradies",
                        "Spa-Bereich",
                        "kulinarisches Erlebnis",
                        "Wohnmobilstellplatz"
                    ]; // Standard-Wörter für nicht erkannte Seiten
            }

            if (words.length > 0) {
                setTyper(element, words);
            }
        });




        // --------------------------------
        // Beleuchtete Elemente
        // --------------------------------
        document.querySelectorAll(".beleuchtet, .dipl_price_list_item").forEach((beleuchtet) => {
            beleuchtet.addEventListener("pointermove", function (event) {
                const rect = beleuchtet.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                beleuchtet.style.setProperty("--x", x);
                beleuchtet.style.setProperty("--y", y);
                beleuchtet.style.setProperty("--x-px", `${x}px`);
                beleuchtet.style.setProperty("--y-px", `${y}px`);
            });
        });

        // --------------------------------
        // AJAX Suche
        // --------------------------------
        const searchElement = document.querySelector(".dipl_ajax_search");
        if (searchElement) {
            searchElement.addEventListener("click", function (event) {
                event.stopPropagation();
                searchElement.classList.add("clicked");
            });

            document.addEventListener("click", function (event) {
                if (!searchElement.contains(event.target)) {
                    searchElement.classList.remove("clicked");
                }
            });
        }

        // --------------------------------
        // DSGVO-Link ändern
        // --------------------------------
        $("span.et_pb_contact_field_options_list span.et_pb_contact_field_checkbox label a")
            .attr("title", "Weitere Informationen zum Datenschutz")
            .text("Link zur DSGVO");


        // --------------------------------
        // ZUSÄTZLICHE KLASSEN
        // --------------------------------
        $(".et_pb_contact_submit").addClass("fill-top");
        $(".spa--grid a.more-link").addClass("fill-top");
        $(".event--karussell a.act-view-more").addClass("fill-top-white");
        $("a.act-view-more.et_pb_button.et_pb_custom_button_icon").addClass("fill-top");
        $(".spa--grid a.more-link").text("Mehr lesen");






        // --------------------------------
        // Prefetching & Prerendering Optimized
        // --------------------------------
        const prefetchLinks = new Map(); // ✅ Speichert bereits vorgeladene Links
        const cacheSize = 2; // ✅ Maximale Anzahl gespeicherter Prefetch-Links
        const prerenderThreshold = 500; // ✅ Wartezeit für Prerender
        let hoverTimer;

        // ✅ Stellt sicher, dass URLs immer mit `/` enden, um 301-Weiterleitungen zu vermeiden
        function normalizeUrl(url) {
            try {
                let normalized = new URL(url, document.baseURI);
                if (!normalized.pathname.endsWith('/')) {
                    normalized.pathname += '/';
                }
                return normalized.href;
            } catch (e) {
                return url;
            }
        }

        // ✅ Prefetching (falls der Link noch nicht vorgeladen wurde)
        function prefetchUrl(url) {
            url = normalizeUrl(url);
            if (prefetchLinks.has(url)) return;

            const prefetch = document.createElement("link");
            prefetch.rel = "prefetch";
            prefetch.href = url;
            document.head.appendChild(prefetch);

            console.log(`✅ Prefetching gestartet: ${url}`); // Debug-Log

            prefetchLinks.set(url, prefetch);

            // ✅ Entferne das älteste Prefetch-Element, falls Cache-Größe überschritten wird
            if (prefetchLinks.size > cacheSize) {
                const firstUrl = prefetchLinks.keys().next().value;
                document.querySelector(`link[href="${firstUrl}"]`)?.remove();
                prefetchLinks.delete(firstUrl);
            }
        }

        // ✅ Prerendering mit Fallback zu Prefetch, falls nicht unterstützt
        function prerenderUrl(url) {
            url = normalizeUrl(url);
            if (document.querySelector(`link[rel="prerender"][href="${url}"]`)) return;

            console.log(`🚀 Prerender gestartet: ${url}`); // Debug-Log

            const prerender = document.createElement("link");
            prerender.rel = "prerender";
            prerender.as = "document";
            prerender.href = url;
            document.head.appendChild(prerender);

            // ✅ Falls der Browser kein Prerendering unterstützt, stattdessen Prefetch verwenden
            if (!document.querySelector(`link[rel="prerender"][href="${url}"]`)) {
                console.log(`⚠️ Prerender nicht unterstützt – Fallback auf Prefetch: ${url}`);
                prefetchUrl(url);
            }
        }

        // ✅ Direktes Prerendering beim `mousedown`, falls der User sofort klickt
        document.body.addEventListener("mousedown", function (event) {
            const link = event.target.closest("a[href]");
            if (!link) return;

            const url = new URL(link.href, document.baseURI);
            if (url.origin !== location.origin) return;

            prerenderUrl(url.href);
        });

        // ✅ Prefetch & Prerender beim `pointerover`
        document.body.addEventListener("pointerover", function (event) {
            const link = event.target.closest("a[href]");
            if (!link) return;

            const url = new URL(link.href, document.baseURI);
            if (url.origin !== location.origin) return;

            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(() => prerenderUrl(url.href), prerenderThreshold);
            prefetchUrl(url.href);
        });

        // ✅ Falls der User weggeht, breche den Hover-Timer ab
        document.body.addEventListener("pointerout", function (event) {
            if (event.target.closest("a[href]")) {
                clearTimeout(hoverTimer);
            }
        });


        // --------------------------------
        // Geöffnet und Geschlossen TEXT (.open--js und .closed--js)
        // --------------------------------
        (function() {
            function timeToMinutes(t) {
              const [h, m] = t.split(':').map(Number);
              return h * 60 + m;
            }
          
            const openingHours = {
              "Montag":    [{ start: "06:00", end: "08:00" }, { start: "10:00", end: "21:00" }],
              "Dienstag":  [{ start: "10:00", end: "21:00" }],
              "Mittwoch":  [{ start: "06:00", end: "08:00" }, { start: "10:00", end: "21:00" }],
              "Donnerstag":[{ start: "10:00", end: "21:00" }],
              "Freitag":   [{ start: "06:00", end: "08:00" }, { start: "10:00", end: "22:00" }],
              "Samstag":   [{ start: "09:00", end: "22:00" }],
              "Sonntag":   [{ start: "09:00", end: "21:00" }]
            };
          
            function updateStatus() {
              const now = new Date();
              const days = ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"];
              const today = days[now.getDay()];
              const nowMinutes = now.getHours() * 60 + now.getMinutes();
              let isOpen = false;
              (openingHours[today] || []).forEach(slot => {
                if (nowMinutes >= timeToMinutes(slot.start) && nowMinutes <= timeToMinutes(slot.end)) {
                  isOpen = true;
                }
              });
              document.querySelectorAll('.open--js').forEach(el => el.style.display = isOpen ? 'block' : 'none');
              document.querySelectorAll('.closed--js').forEach(el => el.style.display = !isOpen ? 'block' : 'none');
            }
          
            updateStatus();
            setInterval(updateStatus, 60000);
        })();
          


        // --------------------------------
        // STARTSEITE TEXT
        // --------------------------------  
        (function() {
            const timeSlots = [
              { className: 'morgen--text',    start: 6,  end: 12 },
              { className: 'mittag--text',    start: 12, end: 17 },
              { className: 'nachmittag--text',start: 17, end: 21 },
              { className: 'abend--text',     start: 21, end: 1  },
              { className: 'nacht--text',     start: 1,  end: 6  }
            ];
            
            // Funktion, die prüft, ob currentHour in den angegebenen Zeitbereich fällt
            function inTimeSlot(currentHour, start, end) {
              return start < end 
                ? currentHour >= start && currentHour < end 
                : currentHour >= start || currentHour < end;
            }
            
            // Aktualisiert alle Elemente je Zeitbereich
            function updateTimeSlotStatus() {
              const now = new Date();
              const hour = now.getHours();
              timeSlots.forEach(slot => {
                const showElement = inTimeSlot(hour, slot.start, slot.end);
                document.querySelectorAll('.' + slot.className)
                        .forEach(el => el.style.display = showElement ? 'block' : 'none');
              });
            }
            
            updateTimeSlotStatus();
            setInterval(updateTimeSlotStatus, 60000);
        })();
          




        // --------------------------------
        // BILD SLIDER
        // --------------------------------
        (function() { 
            const sliderWrapper = document.getElementById('randomSlideWrapper');
            if (!sliderWrapper) return;
            
            const slidesContainer = sliderWrapper.querySelector('.random-slides-container');
        
            // Array mit den 20 Bildpfaden
            let imagePaths = [];
            for (let i = 1; i <= 20; i++) {
                imagePaths.push(`/wp-content/uploads/2025/02/schwimm-${i}-1.jpg`);
            }
        
            // Shuffle-Funktion (Durstenfeld)
            function shuffleArray(array) {
                for (let i = array.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [array[i], array[j]] = [array[j], array[i]];
                }
            }
            shuffleArray(imagePaths);
        
            // Slides bauen
            imagePaths.forEach(src => {
                const slideDiv = document.createElement('div');
                slideDiv.classList.add('random-slide');
        
                const imgElem = document.createElement('img');
                imgElem.src = src;
                slideDiv.appendChild(imgElem);
        
                slidesContainer.appendChild(slideDiv);
            });
        
            let currentIndex = 0;
            const totalSlides = imagePaths.length;
            const intervalTime = 5000; // 5s pro Slide
        
            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalSlides;
                slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
            }
        
            // Autoplay
            setInterval(nextSlide, intervalTime);
        })();
        
        

        // Mobile
        function setupLoadMore(containerSelector, buttonSelector, initialShow, revealCount) {
            let $container = $(containerSelector),
                $articles = $container.find("article"),
                $button = $(buttonSelector);
    
            // Anfangszustand: nur die ersten Artikel zeigen
            $articles.hide().filter(`:nth-child(-n+${initialShow})`).show();
    
            $button.on("click", function(e) {
                e.preventDefault();
    
                let old_show = initialShow;
                initialShow += revealCount;
    
                // Neu hinzukommende Artikel langsam einblenden
                $articles
                    .filter(`:nth-child(n+${old_show+1}):nth-child(-n+${initialShow})`)
                    .fadeIn("slow");
    
                // Button ausblenden, wenn alle Artikel sichtbar sind
                if ($articles.filter(":hidden").length === 0) {
                    $button.hide();
                }
            });
        }


        // --------------------------------
        // LOAD MORE
        // --------------------------------
        if (window.matchMedia('(max-width: 767px)').matches) {
            setupLoadMore(".pa-blog-load-more", "#pa_load_more", 3, 2);
            setupLoadMore(".pa-spa-load-more", "#pa_spa_load_more", 3, 2);
        } else {
            setupLoadMore(".pa-blog-load-more", "#pa_load_more", 1, 2);
            setupLoadMore(".pa-spa-load-more", "#pa_spa_load_more", 1, 2);
        }


        // --------------------------------
        // BILD SLIDER 2
        // --------------------------------
        (function() {
            const sliderWrapper2 = document.getElementById('randomSlideWrapper2');
            if (!sliderWrapper2) return;
        
            const slidesContainer2 = sliderWrapper2.querySelector('.random-slides-container-2');
        
            // Array mit Bildpfaden 1–6
            let imagePaths2 = [];
            for (let i = 1; i <= 6; i++) {
                imagePaths2.push(`/wp-content/uploads/2025/02/schwimm-${i}-1.jpg`);
            }
        
            // Shuffle-Funktion
            function shuffleArray(array) {
                for (let i = array.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [array[i], array[j]] = [array[j], array[i]];
                }
            }
            shuffleArray(imagePaths2);
        
            // Slides erstellen
            imagePaths2.forEach(src => {
                const slideDiv2 = document.createElement('div');
                slideDiv2.classList.add('random-slide-2');
        
                const imgElem2 = document.createElement('img');
                imgElem2.src = src;
                slideDiv2.appendChild(imgElem2);
        
                slidesContainer2.appendChild(slideDiv2);
            });
        
            let currentIndex2 = 0;
            const totalSlides2 = imagePaths2.length;
            const intervalTime2 = 5000; // 5s pro Slide
        
            function nextSlide2() {
                currentIndex2 = (currentIndex2 + 1) % totalSlides2;
                slidesContainer2.style.transform = `translateX(-${currentIndex2 * 100}%)`;
            }
        
            // Autoplay starten
            setInterval(nextSlide2, intervalTime2);
        })();
        

    });
});
