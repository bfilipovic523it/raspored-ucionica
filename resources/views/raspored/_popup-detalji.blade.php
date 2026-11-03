<div class="modal fade" id="popup-detalji" tabindex="-1" data-url-termini="{{ url('/raspored/termini') }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalji-naziv"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
            </div>
            <div class="modal-body">
                <div id="detalji-greska" class="alert alert-danger d-none"></div>

                <dl class="row mb-0">
                    <dt class="col-4">Učionica</dt>
                    <dd class="col-8" id="detalji-ucionica"></dd>

                    <dt class="col-4">Datum</dt>
                    <dd class="col-8" id="detalji-datum"></dd>

                    <dt class="col-4">Vreme</dt>
                    <dd class="col-8" id="detalji-vreme"></dd>

                    <dt class="col-4">Nastavnik</dt>
                    <dd class="col-8" id="detalji-nastavnik"></dd>

                    <dt class="col-4">Status</dt>
                    <dd class="col-8" id="detalji-status"></dd>

                    <dt class="col-4">Opis</dt>
                    <dd class="col-8 mb-0" id="detalji-opis"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                @if (auth()->user()->jeAdmin())
                    <span class="me-auto text-muted small">Promeni status:</span>
                    <button type="button" class="btn btn-success btn-sm" data-status="potvrdjen">Potvrdi</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-status="odbijen">Odbij</button>
                    <button type="button" class="btn btn-warning btn-sm" data-status="ceka">Na čekanju</button>
                @else
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                @endif
            </div>
        </div>
    </div>
</div>
