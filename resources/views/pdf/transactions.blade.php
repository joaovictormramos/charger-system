<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
    h1 { color: #FF8400; font-size: 18px; margin-bottom: 4px; }
    .subtitle { color: #888; font-size: 11px; margin-bottom: 20px; }
    .summary { display: flex; gap: 20px; margin-bottom: 20px; }
    .summary-box { background: #f9f9f9; border-radius: 8px; padding: 10px 16px; flex: 1; text-align: center; }
    .summary-box p { margin: 0; }
    .summary-box .value { font-size: 16px; font-weight: bold; color: #FF8400; }
    .summary-box .label { font-size: 10px; color: #888; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #FF8400; color: white; padding: 8px; text-align: left; font-size: 11px; }
    td { padding: 7px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
    tr:nth-child(even) td { background: #fafafa; }
</style>
</head>
<body>
    <h1>Relatório de Transações</h1>
    <p class="subtitle">
        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @if($charger) · {{ $charger->name ?? $charger->identifier }} @endif
    </p>

    <div class="summary">
        <div class="summary-box">
            <p class="value">{{ $totalSessions }}</p>
            <p class="label">Sessões</p>
        </div>
        <div class="summary-box">
            <p class="value">{{ number_format($totalKwh, 1, ',', '.') }}</p>
            <p class="label">kWh consumidos</p>
        </div>
        <div class="summary-box">
            <p class="value">R$ {{ number_format($totalCost / 100, 2, ',', '.') }}</p>
            <p class="label">Faturamento</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Carregador</th>
                <th>Tipo</th>
                <th>kWh</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
            <tr>
                <td>{{ $t->start_time->format('d/m/Y H:i') }}</td>
                <td>{{ $t->charger->name ?? $t->charger->identifier }}</td>
                <td>{{ $t->rfid_card_id ? 'RFID' : 'Pix' }}</td>
                <td>{{ number_format($t->energy_kwh, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($t->total_cost / 100, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>