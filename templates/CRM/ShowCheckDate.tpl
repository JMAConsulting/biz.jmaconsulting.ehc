{if $checkDate}
{literal}
    <script type="text/javascript">
        CRM.$(function ($) {
            var checkDate = '{/literal}{$checkDate}{literal}';
            $('<th>Date Check Written</th>').insertAfter('table.selector tbody tr th:nth-child(3)');
            $('<td>'+checkDate+'</td>').insertAfter('table.selector tbody tr.odd-row td:nth-child(3)');
        });
    </script>
{/literal}
{/if}