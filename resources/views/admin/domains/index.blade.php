@extends('layouts.admin')

@section('title', 'Domains & DNS')

@section('content-header')
    <h1>Domains & DNS <small>Manage domains and DNS records</small></h1>
@endsection

@section('content')
    @if (!$cloudflareConfigured)
        <div class="alert alert-warning">
            Cloudflare is not configured. Add <code>CLOUDFLARE_API_TOKEN</code> to <code>.env</code> with Zone:Read and DNS:Edit permissions before creating live DNS records.
        </div>
    @endif

    <div class="row">
        <div class="col-sm-4">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Add domain</h3></div>
                <form method="POST" action="{{ route('admin.domains.store') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="domain-name">Domain name</label>
                            <input id="domain-name" name="name" class="form-control" placeholder="example.com" value="{{ old('name') }}" required>
                            <p class="text-muted small">Use the root zone name, without a protocol or path.</p>
                        </div>
                        <div class="form-group">
                            <label for="zone-id">Cloudflare Zone ID <small>(optional)</small></label>
                            <input id="zone-id" name="zone_id" class="form-control" placeholder="Auto-detected from Cloudflare">
                        </div>
                    </div>
                    <div class="box-footer"><button class="btn btn-primary">Add domain</button></div>
                </form>
            </div>
        </div>
        <div class="col-sm-8">
            @forelse ($domains as $domain)
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-globe"></i> {{ $domain->name }}</h3>
                        <div class="box-tools pull-right">
                            <form method="POST" action="{{ route('admin.domains.destroy', $domain) }}" onsubmit="return confirm('Delete this domain and its local records?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-xs">Delete domain</button>
                            </form>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted small">Zone ID: {{ $domain->zone_id ?: 'not configured' }}</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed">
                                <thead><tr><th>Type</th><th>Name</th><th>Content</th><th>TTL</th><th></th></tr></thead>
                                <tbody>
                                @forelse ($domain->records as $record)
                                    <tr>
                                        <td><code>{{ $record->type }}</code></td>
                                        <td>{{ $record->name }}</td>
                                        <td class="text-break">{{ $record->content }}</td>
                                        <td>{{ $record->ttl === 1 ? 'Auto' : $record->ttl }}</td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('admin.domains.records.destroy', $record) }}" onsubmit="return confirm('Delete this DNS record?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No DNS records managed yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <form method="POST" action="{{ route('admin.domains.records.store', $domain) }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-2"><label>Type</label><select name="type" class="form-control"><option>A</option><option>AAAA</option><option>CNAME</option><option>TXT</option><option>MX</option><option>SRV</option></select></div>
                                <div class="col-sm-3"><label>Name</label><input name="name" class="form-control" placeholder="@ or play" required></div>
                                <div class="col-sm-4"><label>Content</label><input name="content" class="form-control" placeholder="192.0.2.10" required></div>
                                <div class="col-sm-2"><label>TTL</label><select name="ttl" class="form-control"><option value="1">Auto</option><option value="300">5 min</option><option value="3600">1 hour</option><option value="86400">1 day</option></select></div>
                                <div class="col-sm-1"><label>&nbsp;</label><button class="btn btn-success btn-block">Add</button></div>
                            </div>
                            <div class="checkbox"><label><input type="checkbox" name="proxied" value="1"> Proxied</label></div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="box box-default"><div class="box-body text-center text-muted">No domains configured yet.</div></div>
            @endforelse
        </div>
    </div>
@endsection
