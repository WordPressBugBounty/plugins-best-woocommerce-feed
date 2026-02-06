(function ($) {
  "use strict";

  $(document).ready(function () {
    var main_tour = new Shepherd.Tour();
    var guide_translation = window?.rexOnboardingJs || {};

    // Inject Shepherd styling CSS
    var shepherdCustomStyles = document.createElement("style");
    shepherdCustomStyles.innerHTML =
      `
        .shepherd-enabled.shepherd-element {
            margin: 10px !important;
        }


        .shepherd-element {
            max-width: 500px;
        }
        
        .shepherd-content {
            border-radius: 10px !important;
            background: #F3F0FF !important;
        }
        
    [data-shepherd-step-id="filter_tab_close"] .shepherd-content, [data-shepherd-step-id="settings_tab_close"] .shepherd-content {
      padding-right: 12px !important;
    }
        
    [data-shepherd-step-id="feed_publish_celebration"] .shepherd-content, [data-shepherd-step-id="feed_publish_celebration_final"] .shepherd-content,
    [data-shepherd-step-id="config_table_celebration"] .shepherd-content, [data-shepherd-step-id="additional_feed_attr_celebration"] .shepherd-content,
    [data-shepherd-step-id="apply_filter_celebration"] .shepherd-content {
            background-image: url('` +
      (window?.rexOnboardingJs?.step1_bg_image || "") +
      `') !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        
        .shepherd-text {
            padding: 0 !important;
        }
        
        p.rex-feed-guided-tour-title {
            font-weight: 600;
            font-size: 20px;
            line-height: 1.2;
            letter-spacing: 0.2px;
            text-align: center;
            color:#216DF0;

            margin: 20px;
        }

        p.rex-feed-guided-tour-title-left{
          font-weight: 600;
          font-size: 20px;
          line-height: 1.2;
          letter-spacing: 0.2px;
          color: #216DF0;
          text-align: left;
          margin: 15px 15px 15px 15px !important;
        }

        .rex-pfm-instructions {
            margin: 10px;
        }

        p.rex-feed-guided-tour-description {
            font-weight: 400;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.2px;
            text-align: center;
            color: #333333;
            margin: 20px !important;
        }

        p.rex-feed-guided-tour-description span {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.2px;
            color: #333333;
        }

        .shepherd-progress-container {
            width: 100%;
            height: 4px;
            background-color: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .shepherd-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4169e1, #5c7cfa);
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .shepherd-footer {
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            display: block !important;
        }
        
        .shepherd-buttons {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        a.udp-tour-end.shepherd-button.shepherd-button-previous.shepherd-button {
            width: 130px !important;
            height: 32px;
            border-radius: 5px;
            border: 1px solid #333333;
            gap: 10px;
            font-weight: 400;
            font-size: 14px;
            line-height: 1.6;
            letter-spacing: 0.2px;
            text-align: center;
            color: #333333 !important;
            background: #FFFFFF !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer !important;
            text-decoration: none !important;
        }
        
        .shepherd-buttons li {
            flex: 0 0 auto !important;
            margin-left: 8px !important;
            padding: 0 !important;
            margin-bottom: 15px !important;
        }

        
        .shepherd-footer button.shepherd-button {
            display: none !important;
        }
        
        .shepherd-step-counter {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            padding: 0 15px;
        }

        a.shepherd-button.udp-tour-end {
            width: 130px !important;
            height: 32px;
            border-radius: 5px;
            border: 1px solid #333333;
            gap: 10px;
            font-weight: 400;
            font-size: 14px;
            line-height: 1.6;
            letter-spacing: 0.2px;
            text-align: center;
            color: #333333 !important;
            background: #FFFFFF !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer !important;
            text-decoration: none !important;
        }
        
        a.shepherd-button.udp-tour-end::before {
            display: none !important;
            content: none !important;
        }
        
        a.shepherd-button.udp-tour-end:hover {
            background: #f5f5f5 !important;
        }

        a.shepherd-button.button.button-primary {
            display: inline-flex !important;
            align-items: center;
            margin-left: 8px;
            width: 130px !important;
            height: 32px !important;
            border-radius: 5px;
            font-weight: 400;
            font-size: 14px;
            line-height: 1.6;
            letter-spacing: 0.2px;
            justify-content: center !important;
            background: #3F04FE !important;
            border: none !important;
            color: #FFFFFF !important;
            transition: all 0.2s ease !important;
            gap: 4px;
        }
        
        a.shepherd-button.button.button-primary:hover {
            background: #3503D9 !important;
        }
        
        .arrow-icon {
            display: inline-flex;
            align-items: center;
            width: 12px;
            height: 12px;
            margin-left: 4px;
        }
        
        .arrow-icon svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .shepherd-has-title .shepherd-content .shepherd-header {
            background: transparent;
            padding: 0;
        }

        .shepherd-cancel-icon {
            display: block !important;
        }
        
        .shepherd-cancel-link {
            font-size: 20px;
            color: #666;
            text-decoration: none;
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .shepherd-cancel-link:hover {
            color: #333;
        }

        .how-to-section {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 14px;
            margin-left: 11px;
            margin-top: 13px;
            margin: 15px 12px 12px 10px;
        }
        
        .how-to-section strong {
            color: #333;
        }
        
        .help-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px !important;
            height: 14px !important;
            background-color: #216DF0 !important;
            color: white !important;
            border-radius: 50%;
            font-size: 12px !important;
            font-weight: bold !important;
            cursor: help;
        }

        ol.rex-pfm-steps {
            margin: 10px 0px 15px 30px;
        }

        ol.rex-pfm-steps-addition-feed-attr {
            margin: 10px 0px 10px 25px;
        }
        `;
    document.head.appendChild(shepherdCustomStyles);

    // Set configuration table background color
    $(
      ".post-type-product-feed #rex_feed_config_heading table#config-table"
    ).css("background-color", "#f6f5fa");

    // Initialize tour after delay
    setTimeout(function () {
      const steps = get_tour_steps();
      create_tour(steps);
    }, 2000);

    $("body.post-type-product-feed").addClass("shepherd-active");

    // Tour options
    main_tour.options.defaults = {
      classes:
        "shepherd-theme-arrows-plain-buttons rex-feed-shepherd-container shadow-md bg-purple-dark",
      showCancelLink: true,
      useModalOverlay: true,
      scrollTo: { behavior: "smooth", block: "center" },
      tetherOptions: {
        constraints: [
          {
            to: "scrollParent",
            attachment: "together",
            pin: false,
          },
        ],
      },
    };

    var next_button_text = guide_translation?.next_button?.title || "Next";
    var prev_button_text = guide_translation?.prev_button?.title || "Previous";
    var done_button_text = guide_translation?.done_button?.title || "Done";
    var skip_button_text =
      guide_translation?.skip_button_text?.title || "Skip Tour";

    /**
     * Add progress bar and counter to footer
     */
    function addProgressToFooter(stepElement, currentStep, totalSteps) {
      const footer = stepElement.querySelector("footer");
      if (!footer) return;

      let buttonsList = footer.querySelector(".shepherd-buttons");

      if (buttonsList) {
        const existingProgress = footer.querySelector(
          ".shepherd-progress-container"
        );
        const existingCounter = footer.querySelector(".shepherd-step-counter");
        if (existingProgress) existingProgress.remove();
        if (existingCounter) existingCounter.remove();
      }

      const progressContainer = document.createElement("div");
      progressContainer.className = "shepherd-progress-container";

      const progressBar = document.createElement("div");
      progressBar.className = "shepherd-progress-bar";
      const progress = (currentStep / totalSteps) * 100;
      progressBar.style.width = `${progress}%`;
      progressContainer.appendChild(progressBar);

      let counterDiv = document.createElement("div");
      counterDiv.className = "shepherd-step-counter";
      counterDiv.textContent = `${currentStep}/${totalSteps}`;

      if (!buttonsList) {
        const existingButtons = Array.from(
          footer.querySelectorAll("button.shepherd-button")
        );
        footer.innerHTML = "";
        const hiddenContainer = document.createElement("div");
        hiddenContainer.style.display = "none";
        existingButtons.forEach((btn) => hiddenContainer.appendChild(btn));
        footer.appendChild(hiddenContainer);

        buttonsList = document.createElement("ul");
        buttonsList.className = "shepherd-buttons";

        existingButtons.forEach((button, index) => {
          const li = document.createElement("li");
          const anchor = document.createElement("a");
          anchor.className = button.className.replace(
            "shepherd-button",
            "shepherd-button"
          );
          anchor.textContent = button.textContent;
          anchor.style.cursor = "pointer";
          anchor.setAttribute("tabindex", "0");

          anchor.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            button.click();
          });

          anchor.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              button.click();
            }
          });

          if (button.classList.contains("shepherd-button-previous")) {
            const arrowIcon = document.createElement("span");
            arrowIcon.className = "arrow-icon";
            arrowIcon.innerHTML = `<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.96973 0.81L1.24976 4.53003L4.96973 8.25" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.25 4.52979H1.25" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>`;
            anchor.prepend(arrowIcon);
          }

          if (button.classList.contains("button-primary")) {
            const arrowIcon = document.createElement("span");
            arrowIcon.className = "arrow-icon";
            arrowIcon.innerHTML = `<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.03027 8.19L10.7502 4.46997L7.03027 0.75" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M0.75 4.47021H10.75" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>`;
            anchor.appendChild(arrowIcon);
          }

          li.appendChild(anchor);
          buttonsList.appendChild(li);

          if (index === 0 && existingButtons.length > 1) {
            const counterLi = document.createElement("li");
            counterLi.appendChild(counterDiv);
            buttonsList.appendChild(counterLi);
          }
        });

        if (existingButtons.length === 1) {
          const counterLi = document.createElement("li");
          counterLi.appendChild(counterDiv);
          buttonsList.appendChild(counterLi);
        }

        footer.appendChild(progressContainer);
        footer.appendChild(buttonsList);
      } else {
        footer.insertBefore(progressContainer, buttonsList);
        const buttons = buttonsList.querySelectorAll("li");
        const counterLi = document.createElement("li");
        counterLi.appendChild(counterDiv);
        if (buttons.length > 1) {
          buttonsList.insertBefore(counterLi, buttons[buttons.length - 1]);
        } else {
          buttonsList.appendChild(counterLi);
        }
      }
    }

    /**
     * Create dynamic tour with precise drawer control
     */
    function create_tour(tour_steps) {
      $.each(tour_steps, function (_index, item) {
        let buttons = [];

        // Previous Button
        if (item["prev_button"] !== "") {
          buttons.push({
            action: function () {
              const currentId = _index;

              if (currentId === "product_filter") {
                scroll_to_popup_down();
              }

              if (currentId === "feed_publish") {
                scroll_to_popup();
              }

              if (currentId === "feed_title") {
                main_tour.cancel();
                return;
              }

              if (currentId === "tour_end_feed_publish") {
                $("#rex-feed-settings-btn").trigger("click");
                scroll_to_popup();
                setTimeout(() => main_tour.show("settings_tab_close"), 500);
                return;
              }

              if (currentId === "settings_tab_close") {
                $("#rex_feed_settings_modal_close_btn").trigger("click");
                setTimeout(() => main_tour.show("feed_settings"), 500);
                return;
              }

              if (currentId === "feed_settings") {
                if ($("#rex_feed_settings_modal").is(":visible")) {
                  $("#rex_feed_settings_modal_close_btn").trigger("click");
                }

                $("#rex-pr-filter-btn").trigger("click");
                setTimeout(() => main_tour.show("filter_tab_close"), 500);
                return;
              }

              if (currentId === "filter_tab_close") {
                $("#rex_feed_filter_modal_close_btn").trigger("click");
                setTimeout(() => main_tour.show("product_filter"), 1000);
                return;
              }

              if (currentId === "product_filter") {
                if ($("#rex_feed_filter_modal").is(":visible")) {
                  $("#rex_feed_filter_modal_close_btn").trigger("click");
                }
                setTimeout(() => main_tour.show("additional_feed_attr"), 1000);
                return;
              }
              main_tour.back();
            },
            classes:
              "udp-tour-end shepherd-button " +
              (_index === "feed_title" ? "" : "shepherd-button-previous"),
            text: item["prev_button"],
          });
        }

        // Next Button
        if (item["next_button"] !== "") {
          buttons.push({
            action: function () {
              const currentId = _index;

              if (
                currentId === "config_table_celebration" ||
                currentId === "tour_end_feed_publish"
              ) {
                scroll_to_popup_down();
              }

              if (currentId === "additional_feed_attr_celebration") {
                scroll_to_popup();
              }

              if (currentId === "product_filter") {
                if (!$("#rex_feed_filter_modal").is(":visible")) {
                  $("#rex-pr-filter-btn").trigger("click");
                }
                setTimeout(() => main_tour.show("apply_filter_celebration"), 500);
                return;
              }

              if (currentId === "filter_tab_close") {
                $("#rex_feed_filter_modal_close_btn").trigger("click");
                setTimeout(() => main_tour.next(), 300);
                return;
              }

              if (currentId === "feed_settings") {
                if (!$("#rex_feed_settings_modal").is(":visible")) {
                  $("#rex-feed-settings-btn").trigger("click");
                }
                setTimeout(() => main_tour.show("settings_tab_close"), 500);
                return;
              }

              if (currentId === "settings_tab_close") {
                $("#rex_feed_settings_modal_close_btn").trigger("click");
                setTimeout(function(){
                  main_tour.next()
                  scroll_to_popup_down();              
                }, 300);
                return;
              }

              if (currentId === "tour_end_feed_publish") {
                main_tour.next();
                return;
              }

              if (currentId === "feed_publish_celebration_final") {
                main_tour.complete();
                return;
              }

              main_tour.next();
            },
            classes: "button button-primary",
            text: item["next_button"],
          });
        }

        main_tour.addStep({
          title: item["title"],
          text: item["desc"],
          attachTo: {
            element: item["attach_element"],
            on: item["attach_element_on"],
          },
          buttons: buttons,
          id: _index,
          when: {
            show: function () {
              const currentStepIndex = main_tour.steps.indexOf(this) + 1;
              addProgressToFooter(
                this.el,
                currentStepIndex,
                main_tour.steps.length
              );

              if (_index === "merchant_name_type") {
                const merchant = $("select#rex_feed_merchant").val();
                const nextBtn = $(this.el).find(".button-primary");
                if (merchant === "-1" || !merchant) {
                  nextBtn
                    .prop("disabled", true)
                    .css({ opacity: "0.5", cursor: "not-allowed" });
                } else {
                  nextBtn
                    .prop("disabled", false)
                    .css({ opacity: "1", cursor: "pointer" });
                }
              }
            },
          },
        });
      });

      main_tour.start();
      main_tour.on("cancel", complete_tour);
      main_tour.on("complete", complete_tour);

      function complete_tour() {
        $("body.post-type-product-feed").removeClass("shepherd-active");
        var param = getParameterByName("tour_guide");
        if (param === "1") {
          var newUrl = updateParam("tour_guide", 0);
          if (
            window.history &&
            typeof window.history.pushState === "function"
          ) {
            window.history.pushState({ path: newUrl }, "", newUrl);
          }
        }
      }

      var scroll_to_popup = function () {
        $("html, body").animate({ scrollTop: 0 }, 500);
      };

      var scroll_to_popup_down = function () {
        $("html, body").animate(
          { scrollTop: $(document).height() - $(window).height() },
          500
        );
      };
    }

    /**
     * Tour steps
     */
    function get_tour_steps() {
      return {
        feed_title: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>${
            guide_translation?.feed_title?.title || "Give A Name To This Feed"
          }</p><p class='rex-feed-guided-tour-description'>${"Enter a name for your feed. This helps you recognize and manage it later."} <span class="help-icon" title="Use a descriptive name like 'Google Shopping – Shoes' so you can easily identify it later.">?</span></p>`,
          attach_element: ".post-type-product-feed #post-body-content",
          attach_element_on: "bottom",
          next_button: next_button_text,
          prev_button: skip_button_text,
        },
        merchant_name_type: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Choose Merchant & File Type</p><p class='rex-feed-guided-tour-description'>Select the target merchant/marketplace and the file type for your feed. <span class="help-icon" title='XML works best for Google Shopping, while CSV is often used for Facebook or manual uploads.'>?</span></p>`,
          attach_element: ".post-type-product-feed #rex_feed_conf",
          attach_element_on: "bottom",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        config_table: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Map Product Data</p><p class='rex-feed-guided-tour-description'>Here you'll see the required attributes for your selected merchant. Most are already mapped — but you can adjust them if needed. <span class="help-icon" title='Double-check “Product ID,” “Title,” and “Price.” These are essential for most merchants.'>?</span></p>`,
          attach_element:
            ".post-type-product-feed #rex_feed_config_heading table#config-table",
          attach_element_on: "top",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        config_table_celebration: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Awesome!</p><p class='rex-feed-guided-tour-description'>Your products are ready for the feed.</p>`,
          attach_element: ".post-type-product-feed #rex_feed_config_heading table#config-table",
          attach_element_on: "top",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        feed_publish: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title-left'>Generate Your Feed</p><div class="rex-pfm-instructions">
                    <div class="how-to-section">
                        <strong>Click Publish to create your feed.</strong>
                        <span class="help-icon" title='For your first feed, try publishing right away to confirm setup — you can edit later.'>?</span>
                    </div>
                    <ol class="rex-pfm-steps">
                        <li>The tour will end if you publish now.</li>
                        <li>Or, click Next to explore more advanced options before generating. <span class="help-icon" title='Adding Brand, GTIN, or MPN helps improve ad performance and approval rates.'>?</span></li>
                    </ol>
                    </div>`,
          attach_element: ".post-type-product-feed #rex-bottom-publish-btn",
          attach_element_on: "top",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        feed_publish_celebration: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Awesome!</p><p class='rex-feed-guided-tour-description'>Your products are ready for the feed.</p>`,
          attach_element: ".post-type-product-feed #rex-bottom-publish-btn",
          attach_element_on: "top",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        additional_feed_attr: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title-left'>Want more details in your feed?</p><div class="rex-pfm-instructions">
                    <ol class="rex-pfm-steps-addition-feed-attr">
                        <li><strong>Add New Attribute</strong> → pick from the merchant's supported fields.</li>
                        <li><strong>Add Custom Attribute</strong> → create your own attribute and map it. <span class="help-icon" title='Adding attributes like Brand, GTIN, or MPN not only improves ad performance but also allows you to use them later in Custom Filters to include/exclude products.'>?</span></li>
                    </ol>
                    </div>`,
          attach_element: ".post-type-product-feed .rex-feed-attr-btn-area",
          attach_element_on: "right",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        additional_feed_attr_celebration: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Great!</p><p class='rex-feed-guided-tour-description'>Your feed now includes only the products you want to advertise.</p>`,
          attach_element: ".post-type-product-feed .rex-feed-attr-btn-area",
          attach_element_on: "top",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        product_filter: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title-left'>Apply Product Filters</p><div class="rex-pfm-instructions">
                    <div class="how-to-section">
                        <strong>Filter which products go into your feed:</strong>
                        <span class="help-icon" title='Use Custom Filters to include or exclude products based on GTIN, Brand, or other attributes — giving you precise control over which products are advertised.'>?</span>
                    </div>
                    <ol class="rex-pfm-steps">
                        <li>Featured products</li>
                        <li>Categories</li>
                        <li>Tags</li>
                        <li>Brands</li>
                        <li>Custom filters</li>
                        <li>(Pro) Advanced filters & rules</li>
                    </ol>
                    </div>`,
          attach_element: ".post-type-product-feed #rex-pr-filter-btn",
          attach_element_on: "bottom",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        apply_filter_celebration: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Nice!</p><p class='rex-feed-guided-tour-description'>Extra product details added — your feed is stronger and more accurate.</p>`,
          attach_element: ".post-type-product-feed #rex-feed-product-filter-save-changes",
          attach_element_on: "left",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        filter_tab_close: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Close Settings</p><p class="rex-feed-guided-tour-description">Click the Save Changes button to return to the attributes list. <span class="help-icon">?</span></p>`,
          attach_element:
            ".post-type-product-feed #rex_feed_filter_modal_close_btn",
          attach_element_on: "bottom",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        feed_settings: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title-left'>Configure Feed Settings</p><div class="rex-pfm-instructions">
                    <div class="how-to-section">
                        <strong>Fine-tune your feed:</strong>
                        <span class="help-icon" title='Enable scheduling to keep your feed synced with product changes automatically.'>?</span>
                    </div>
                    <ol class="rex-pfm-steps">
                        <li>Schedule automatic updates</li>
                        <li>Include out-of-stock products</li>
                        <li>Exclude empty values</li>
                        <li>Control product types</li>
                        <li>Add UTM parameters for tracking</li>
                    </ol>
                    </div>`,
          attach_element: ".post-type-product-feed #rex-feed-settings-btn",
          attach_element_on: "bottom",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        settings_tab_close: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Close The Settings Drawer</p><p class='rex-feed-guided-tour-description'>Once you make any changes, click on the Close button to get back to the Attributes section.</p>`,
          attach_element:
            ".post-type-product-feed #rex_feed_settings_modal_close_btn",
          attach_element_on: "bottom",
          next_button: next_button_text,
          prev_button: prev_button_text,
        },
        tour_end_feed_publish: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title-left'>Publish & Download</p><div class="rex-pfm-instructions">
                    <div class="how-to-section">
                        <strong>Click Publish to generate your feed.</strong>
                        <span class="help-icon" title='If a merchant rejects your feed, recheck product attributes like price, availability, and unique IDs.'>?</span>
                    </div>
                    <ol class="rex-pfm-steps">
                        <li>The tour will end once publishing starts.</li>
                        <li>You'll see a progress bar while the feed is created.</li>
                        <li>When done, click View/Download to access your feed. 🎉</li>
                    </ol>
                    </div>`,
          attach_element: ".post-type-product-feed #rex-bottom-publish-btn",
          attach_element_on: "bottom",
          next_button: next_button_text || "Finish Tour",
          prev_button: prev_button_text,
        },
        feed_publish_celebration_final: {
          title: "",
          desc: `<p class='rex-feed-guided-tour-title'>Success!</p><p class='rex-feed-guided-tour-description'>Your feed is generated and ready to use.</p>`,
          attach_element: ".post-type-product-feed #rex-bottom-publish-btn",
          attach_element_on: "bottom",
          next_button: done_button_text,
          prev_button: prev_button_text,
        },
      };
    }

    // Merchant change
    $(document).on("change", "select#rex_feed_merchant", function () {
      const merchant = $(this).val();
      const nextBtn = $(".shepherd-element .button-primary");
      if (merchant !== "-1" && merchant) {
        nextBtn
          .prop("disabled", false)
          .css({ opacity: "1", cursor: "pointer" });
      } else {
        nextBtn
          .prop("disabled", true)
          .css({ opacity: "0.5", cursor: "not-allowed" });
      }
    });

    // Tour end / publish
    $(document).on(
      "click",
      ".shepherd-header .shepherd-cancel-icon, #publish, #rex-bottom-publish-btn",
      function () {
        let url = window.location.href;
        if (url.includes("tour_guide=1")) {
          window.history.pushState({}, "", url.replace("&tour_guide=1", ""));
        }
        $("body.post-type-product-feed").removeClass("shepherd-active");
        if ($(this).is("#publish, #rex-bottom-publish-btn")) {
          $(".shepherd-cancel-icon").trigger("click");
        }
      }
    );

    function getParameterByName(name, url = window.location.href) {
      name = name.replace(/[\[\]]/g, "\\$&");
      var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
      if (!results) return null;
      if (!results[2]) return "";
      return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    function updateParam(name, value, url = window.location.href) {
      var urlObj = new URL(url);
      urlObj.searchParams.delete(name);
      return urlObj.toString();
    }
  });
})(jQuery);
