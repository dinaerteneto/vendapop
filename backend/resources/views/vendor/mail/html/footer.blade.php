@props(["url"])
<tr>
<td class="footer">
<p>&copy; {{ date("Y") }} <a href="{{ $url }}">{{ config('app.name') }}</a>. Todos os direitos reservados.</p>
</td>
</tr>
