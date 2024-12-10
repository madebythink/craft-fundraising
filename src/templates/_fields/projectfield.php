{% set projects = craft.fundraising.projects.allProjects() %}
<select name="{{ name }}">
  <option value="" {% if not value %}selected{% endif %}></option>
  {% for p in projects %}
    <option value="{{ p.id }}" {% if value == p.id %}selected{% endif %}>{{ p.title }}</option>
  {% endfor %}
</select>
