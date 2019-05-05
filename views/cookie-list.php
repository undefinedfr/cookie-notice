<div class="cookie-list" id="cookie-list">
    <table>
        <thead>
        <tr>
            <th>{% cookie-name %}</th>
            <th>{% cookie-function %}</th>
            <th>{% cookie-type %}</th>
            <th>{% cookie-authorize %}</th>
        </tr>
        </thead>
        <tbody>
        {% cookies-list %}
        </tbody>
    </table>
    <div class="cookie-state {% cookie-state-class %}">
        {% cookie-state %}
    </div>
    <button id="cookie-accept" name="cookie-accept" class="bouton bouton-green wow fadeInLeft">{% cookie-accept %}</button>
    <a href="{% cookie-page %}" id="cookie-more-button" name="cookie-more" class="bouton bouton-orange wow fadeInLeft">{% cookie-more %}</a>
</div>