<div id="cookie-container">
    <div id="cookie-banner" class="cookie-banner">
        <div class="text">
            <p class="title">{% cookie-title %}</p>
            {% cookie-text %}
            <div class="list-button">
                <button id="cookie-accept" name="cookie-accept" class="bouton bouton-green wow fadeInLeft">{% cookie-accept %}</button>
                <a href="{% cookie-page %}" id="cookie-more-button" name="cookie-more" class="bouton bouton-orange wow fadeInLeft">{% cookie-more %}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    if (typeof initCookieFunctions !== "function") {
        function initCookieFunctions(){
            {% cookie-functions-names %}
        }
    }
</script>