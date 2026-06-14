<?php

namespace App\Livewire\Admin;

use App\Models\SecurityEvent;
use App\Models\BannedIp;
use Livewire\Component;
use Livewire\WithPagination;

class SecurityDashboard extends Component
{
    use WithPagination;

    public $search = '';
    public $severity = '';
    public $activeTab = 'events'; // events, banned_ips

    public function unbanIp($id)
    {
        $banned = BannedIp::findOrFail($id);
        $banned->delete();

        SecurityEvent::create([
            'event_type' => 'manual_unban',
            'severity' => 'low',
            'ip_address' => $banned->ip_address,
            'metadata' => ['banned_id' => $id]
        ]);

        $this->dispatch('toast', type: 'info', message: "IP {$banned->ip_address} telah dibuka blokirnya.");
    }

    public function banIp($ip, $reason = 'Manual ban from dashboard')
    {
        BannedIp::firstOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'banned_until' => now()->addYear() // Permanent-ish
            ]
        );

        $this->dispatch('toast', type: 'warning', message: "IP {$ip} telah diblokir.");
    }

    public function clearLogs()
    {
        SecurityEvent::where('severity', 'low')->delete();
        $this->dispatch('toast', type: 'info', message: 'Log tingkat rendah telah dibersihkan.');
    }

    public function render()
    {
        $events = SecurityEvent::query()
            ->when($this->search, fn($q) => $q->where('ip_address', 'like', "%{$this->search}%")->orWhere('payload', 'like', "%{$this->search}%"))
            ->when($this->severity, fn($q) => $q->where('severity', $this->severity))
            ->latest()
            ->paginate(15, pageName: 'events-page');

        $bannedIps = BannedIp::query()
            ->latest()
            ->get();

        $stats = [
            'total_events' => SecurityEvent::count(),
            'critical_events' => SecurityEvent::where('severity', 'critical')->count(),
            'total_banned' => BannedIp::count(),
        ];

        return view('livewire.admin.security-dashboard', [
            'events' => $events,
            'bannedIps' => $bannedIps,
            'stats' => $stats
        ])->layout('layouts.admin');
    }
}
