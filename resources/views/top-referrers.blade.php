@php use Carbon\CarbonInterval; @endphp
<div style="width: 100%;">
    <table style="max-width: 100%; width: 100%;">
        <tr>
            <th style="padding-bottom: 10px;">
                {{ __('Referrer') }}
            </th>
            <th style="padding-bottom: 10px;">
                {{__('Page views')}}
            </th>
            <th style="padding-bottom: 10px;">
                {{__('Unique visitors')}}
            </th>
        </tr>

        @foreach($referrers as $ref)
            @php
                $percent = $total > 0 ? ($ref['pageviews'] / $total) * 100 : 0;
            @endphp

            <tr>
                <td style="padding-bottom: 5px;">
                    <div style="position: relative; padding: 5px 5px;  border-radius: 4px;">

                        <div style="position:absolute; inset:0; width:{{$percent}}%; background-color: rgba(128, 0, 128, 0.2); border-radius:4px; z-index:0;"></div>

                        <div style="position:relative; z-index:1;">
                            @if($ref['referrer_hostname'] == null)
                                <span style="color:black; font-weight: bold;">Direct</span>
                            @else
                                <a style="color: black; text-decoration: none;" href="{{($ref['referrer_hostname'].$ref['referrer_pathname'])??'#'}}" target="_blank">
                                    {{ str($ref['referrer_hostname'])->replace(['https://','http://'],'')->toString() ?: 'Direct' }}
                                </a>
                            @endif
                        </div>

                    </div>
                </td>

                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{$ref['pageviews']}}</div>
                </td>

                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{$ref['uniques']}}</div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
