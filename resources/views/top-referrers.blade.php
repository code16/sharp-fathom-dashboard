@php use Carbon\CarbonInterval; @endphp
<div style="width: 100%;">
    <table style="max-width: 100%; width: 100%;">
        <tr>
            <th style="padding-bottom: 10px;">
                {{ __('Referrer') }}
            </th>
            <th style="padding-bottom: 10px;">
                {{__('Page views')}}
            </th style="padding-bottom: 10px;">
            <th style="padding-bottom: 10px;">
                {{__('Unique visitors')}}
            </th>
        </tr>
        @foreach($referrers as $ref)
            <tr>
                <td style="padding-bottom: 5px;">
                    <div style="padding: 5px 5px; word-break: break-word;  background-color: rgba(128, 0, 128, 0.5); border-radius: 4px;">
                        @if($ref['referrer_hostname'] == null)
                            <span style="color:white; font-weight: bold;">Direct</span>
                        @else
                            <a style="color: white; text-decoration: none;" href="{{($ref['referrer_hostname'].$ref['referrer_pathname'])??'#'}}" target="_blank">{{str($ref['referrer_hostname'])->replace(['https://', 'http://'], '')->toString() ?: 'Direct'}}</a>
                        @endif
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
