/**
 * iRO_JsFilter
 *
 * inspired by: http://kilianvalkhof.com/2010/javascript/how-to-build-a-fast-simple-list-filter-with-jquery/
 *
 * @author Alexander Pape <a.pape@paneon.de>
 */
var iRO_JsFilter = (function ($) {

    /**
     * @type {jQuery}
     */
    var searchWrapper;
    var searchField;
    var searchResultCounter;
    var noResults;

    var moduleInitString = "irojsfilter";

    var MODULE_STATE_FRESH = 0;
    var MODULE_STATE_COMPLETE = 1;

    var selectorJsCounter = ".jsIroJobCount";
    var selectorNoResults = ".jsIroFilterNoResults";
    var lastFilterCount = false;

    var initialize = function(containerSelector, inputSelector){
        searchWrapper = $(containerSelector);
        searchField = $(inputSelector);

        searchResultCounter = $(selectorJsCounter);
        noResults = $(selectorNoResults);

        var initStatus = searchWrapper.data(moduleInitString) || MODULE_STATE_FRESH;

        if(initStatus == MODULE_STATE_FRESH && searchWrapper.length && searchField.length){
            initEvents();

        }
    };

    var updateJsCounter = function(){

        var currentJobCount = $(".job-wrapper:visible").length;

        if(currentJobCount != lastFilterCount){

            searchResultCounter.text(currentJobCount);
            lastFilterCount = currentJobCount;

        }

        if(currentJobCount == 0){
            noResults.show();
        }
        else{
            noResults.hide();
        }
    };

    var initEvents = function(){

        searchField.on("keyup change", function (event) {
            var filterQuery = $(this).val();

            if(filterQuery.length > 0) {
                var matches = $(searchWrapper).find('.job-wrapper:contains(' + filterQuery + ')');
                $('.job-wrapper', searchWrapper).not(matches).hide();
                matches.show();
            }
            else {
                $(searchWrapper).find(".job-wrapper").show();
            }
            updateJsCounter();
            return false;
        });

        $(searchWrapper).data(moduleInitString, MODULE_STATE_COMPLETE);
    };

    return {
        initialize: initialize
    };

}(jQuery));