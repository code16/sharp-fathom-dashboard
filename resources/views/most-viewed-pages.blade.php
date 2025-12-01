@php use Carbon\CarbonInterval; @endphp
<div style="width: 100%;">
    <table style="max-width: 100%;">
        <tr>
            <th style="padding-bottom: 10px;">
                Page
            </th>
            <th style="padding-bottom: 10px;">
                {{ __('Page views') }}
            </th>
            <th style="padding-bottom: 10px;">
                {{ __('Unique visitors') }}
            </th>
            <th style="padding-bottom: 10px;">
                {{ __('Average time on page') }}
            </th>
        </tr>
        @foreach($pages as $page)
            @php
                $pageviews = $page['pageviews'] ?? 0;
                $pct = ($total && $total > 0) ? ($pageviews / $total) * 100 : 0;
                // limite à 100% pour éviter débordement s'il y a des incohérences
                $pct = $pct > 100 ? 100 : $pct;
            @endphp
            <tr>
                <td style="padding-bottom: 5px;">
                    <!-- wrapper positionné pour contenir le background proportionnel -->
                    <div style="position: relative; padding: 5px 5px; border-radius: 4px; overflow: visible; width: 100%;">
                        <!-- background violet proportionnel (absolu, en dessous du texte) -->
                        <div
                            style="
                                position: absolute;
                                left: 0;
                                top: 50%;
                                transform: translateY(-50%);
                                height: calc(100% - 0px); /* laisse le padding du wrapper gérer la hauteur */
                                width: {{ number_format($pct, 4, '.', '') }}%;
                                background-color: rgba(128, 0, 128, 0.2);
                                border-radius: 4px;
                                z-index: 0;
                                pointer-events: none;
                            ">
                        </div>

                        <!-- contenu au-dessus du background (z-index supérieur) -->
                        <a
                            style="position: relative; z-index: 1; color: black; text-decoration: none; display: inline-block;"
                            href="{{ $page['hostname'] . $page['pathname'] ?? '#' }}"
                            target="_blank"
                        >
                            @if(($page['pathname'] ?? null) === '/')
                                {{ $page['hostname'] }}
                            @else
                                {{ $page['pathname'] ?? 'N/A' }}
                            @endif
                        </a>
                    </div>
                </td>

                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{ $pageviews }}</div>
                </td>

                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{ $page['uniques'] }}</div>
                </td>

                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">
                        {{ CarbonInterval::seconds($page['avg_duration'] ?? 0)->cascade()->forHumans(short: true) }}
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
