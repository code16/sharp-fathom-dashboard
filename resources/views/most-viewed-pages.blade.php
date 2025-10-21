@php use Carbon\CarbonInterval; @endphp
<div style="width: 100%;">
    <table style="max-width: 100%; width: 100%;">
        <tr>
            <th style="padding-bottom: 10px;">
                Page
            </th>
            <th style="padding-bottom: 10px;">
                {{__('Page views')}}
            </th>
            <th style="padding-bottom: 10px;">
                {{__('Unique visitors')}}
            </th>
            <th style="padding-bottom: 10px;">
                {{__('Average time on page')}}
            </th>
        </tr>
        @foreach($pages as $page)
            <tr>
                <td style="padding-bottom: 5px;">
                    <div style="padding: 5px 5px; word-break: break-word;  background-color: rgba(128, 0, 128, 0.5); border-radius: 4px;">
                        <a style="color: white; text-decoration: none;" href="{{$page['hostname'].$page['pathname']??'#'}}" target="_blank">
                            @if($page['pathname'] == '/')
                                {{$page['hostname']}}
                            @else
                                {{$page['pathname'] ?? 'N/A'}}
                            @endif
                        </a>
                    </div>
                </td>
                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{$page['pageviews']}}</div>
                </td>
                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{$page['uniques']}}</div>
                </td>
                <td style="padding-bottom: 5px;">
                    <div style="text-align: center; padding-left: 5px; padding-right:5px;">{{CarbonInterval::seconds($page['avg_duration'] ?? 0)->cascade()->forHumans(short: true)}}</div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
