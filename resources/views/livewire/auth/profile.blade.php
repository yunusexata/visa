<form wire:submit="store">
    <div class='row'>
        <div class="col-md-6 mb-4">
            <label>Nama</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-6 mb-4">
            <label>Kode PIC</label>
            {{-- @if ($role == App\Models\User::ROLE_SALES) --}}
            <input type="text" class="form-control" value="{{$pic_code}}" placeholder="Kode PIC" disabled/>
            {{-- @else
                <select class="form-select" wire:model="pic_code">
                    <option value="">-- ISI --</option>
                    @foreach ($pic_code_choice as $item)
                        <option value="{{$item}}">{{$item}}</option>
                    @endforeach
                </select>
            @endif --}}
        </div>
        @if ($role == App\Models\User::ROLE_SALES)
            <div class="col-md-6 mb-4">
                <label>Password Gensen Form</label>
                <input type="text" class="form-control @error('password_gensen_form') is-invalid @enderror" wire:model.blur="password_gensen_form" />

                @error('password_gensen_form')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        @endif
            <div class="col-md-6 mb-4">
                <label>No Whatsapp</label>
                <input type="text" class="form-control @error('no_whatsapp') is-invalid @enderror" wire:model.blur="no_whatsapp" placeholder="No Whatsapp"/>

                @error('no_whatsapp')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        <div class="col-md-6 mb-4">
            <label>Email</label>
            <input type="email" class="form-control" wire:model.blur="email" disabled />
        </div>

        <div class="col-md-6 mb-4">
            <label>Jabatan</label>
            <input type="text" class="form-control" wire:model.blur="role" disabled />
        </div>

        <hr>

        <div class="col-md-12 mb-4">
            <label>Password Lama</label>
            <div class='fst-italic'>*Diisi jika ingin mengubah password</div>
            <input type="password" class="form-control @error('oldPassword') is-invalid @enderror"
                wire:model.blur="oldPassword" autocomplete="new-password"/>

            @error('oldPassword')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-12 mb-4">
            <label>Password Baru</label>
            <div class='fst-italic'>*Diisi jika ingin mengubah password</div>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                wire:model.blur="password" />

            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-12 mb-4">
            <label>Ketik Ulang Password Baru</label>
            <div class='fst-italic'>*Diisi jika ingin mengubah password</div>
            <input type="password" class="form-control @error('retypePassword') is-invalid @enderror"
                wire:model.blur="retypePassword" />

            @error('retypePassword')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

    </div>

    <button type="submit" class="btn btn-success mt-3">
        <i class='ki-duotone ki-check fs-1'></i>
        Simpan Perubahan
    </button>
</form>
