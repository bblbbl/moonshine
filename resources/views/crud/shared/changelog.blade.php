@if($logs = \Leeto\MoonShine\MoonShine::changeLogs($item))
    <div class="my-6 text-lg">@lang('moonshine::ui.last_changes')</div>
    <x-moonshine::table>
        <x-slot:thead>
            <th>
                @lang('moonshine::ui.user')
            </th>
            <th>
                @lang('moonshine::ui.changes')
            </th>
            <th>
                @lang('moonshine::ui.date')
            </th>
        </x-slot:thead>
        <x-slot:tbody>
            @foreach($logs->take(5) as $log)
                <tr>
                    <td>
                        @include('moonshine::ui.badge', [
                            'color' => 'purple',
                            'value' => $log->moonshineUser->name
                        ])
                    </td>
                    <td>
                        <x-moonshine::table>
                            <x-slot:thead>
                    <th>
                        @lang('moonshine::ui.field')
                    </th>
                    <th>
                        @lang('moonshine::ui.before')
                    </th>
                    <th>
                        @lang('moonshine::ui.after')
                    </th>
                    </x-slot:thead>
                    <x-slot:tbody>
                @foreach($log->states_after as $changedField => $changedValue)
                    @if($resource->getField($changedField) && !$resource->getField($changedField) instanceof \Leeto\MoonShine\Fields\Json)
                        <tr>
                            <td>
                                {{ $resource->getField($changedField) ? $resource->getField($changedField)->label() : $changedField }}
                            </td>

                            <td>
                                {{ is_array($log->states_before[$changedField]) ? json_encode($log->states_before[$changedField]) : $log->states_before[$changedField] }}
                            </td>
                            <td>
                                {{ is_array($changedValue) ? json_encode($changedValue) : $changedValue }}
                            </td>
                        </tr>
                    @endif
                @endforeach
        </x-slot:tbody>
    </x-moonshine::table>
    </td>
    <td>
        {{ $log->created_at->format('d.m.Y H:i') }}
    </td>
    </tr>
    @endforeach
    </x-slot:tbody>
    </x-moonshine::table>
@endif
