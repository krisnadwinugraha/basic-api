<?php

namespace App\Models;

use App\Traits\HasFile;
use App\Traits\Selectable;
use App\Traits\WithPaginatedData;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, HasFile, WithPaginatedData, Selectable;

    /**
     * The attributes that are guarded.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that are searchable.
     *
     * @var array<int, string>
     */
    protected $searchable = [
        'nip',
        'name',
        'kta',
        'branch',
        'branch.region',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'gender_label',
        'age',
        'ktp_url',
        'id_card_url',
        'form_summary_url',
        'last_position',
    ];

    /**
     * Get the member gender label.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function genderLabel(): Attribute
    {
        return new Attribute(
            get: fn () => $this->gender === 'male' ? 'Laki-laki' : 'Perempuan',
        );
    }

    /**
     * Get the member age.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function age(): Attribute
    {
        return new Attribute(
            get: fn () => Carbon::parse($this->birth_date)->age,
        );
    }

    /**
     * Get the member ktp file url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function ktpUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->ktp ? asset('storage/' . $this->ktp) : null,
        );
    }

    /**
     * Get the member id card file url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function idCardUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->id_card ? asset('storage/' . $this->id_card) : null,
        );
    }

    /**
     * Get the member form summary file url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function formSummaryUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->form_summary ? asset('storage/' . $this->form_summary) : null,
        );
    }

    /**
     * Member branch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Member regular donation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function regularDonation()
    {
        return $this->hasOne(RegularDonation::class);
    }

    /**
     * Member special donation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function specialDonation()
    {
        return $this->hasOne(SpecialDonation::class);
    }

    /**
     * Member retirement age.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function retirementAge()
    {
        return $this->belongsTo(RetirementAge::class);
    }

    /**
     * Member status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MemberStatus::class, 'member_status_code', 'code');
    }

    /**
     * Member inactive status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inactiveStatus()
    {
        return $this->belongsTo(InactiveStatus::class, 'inactive_status_code', 'code');
    }

    /**
     * Member user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Member positions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get option label
     *
     * @return string
     */
    public function getOptionLabel()
    {
        return $this->name;
    }

    /**
     * Get option value
     *
     * @return string
     */
    public function getOptionValue()
    {
        return (string) $this->id;
    }

    /**
     * Get the member last position.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function lastPosition(): Attribute
    {
        return new Attribute(
            get: fn () => $this->relationLoaded('positions') ?
                $this->positions->whereNull('end_date')->first() :
                $this->positions()->whereNull('end_date')->first(),
        );
    }

    /**
     * Custom query for active members.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('member_status_code', 1);
    }

    /**
     * Custom query for sort.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string $sortKey
     * @param  string $sortOrder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSort(Builder $query, $sortKey, $sortOrder)
    {
        $table = $this->getTable();

        if ($sortKey === 'branch.name') {
            $relationTable = 'branches';
            $field = 'name';

            return $query->select("{$table}.*")
                ->join($relationTable . ' as t', 't.id', '=', "{$table}.branch_id")
                ->orderBy('t.' . $field, $sortOrder);
        } elseif ($sortKey === 'branch.region.name') {
            $relationTable = 'regions';
            $field = 'name';

            return $query->select("{$table}.*")
                ->join('branches as b', 'b.id', '=', "{$table}.branch_id")
                ->join($relationTable . ' as t', 't.id', '=', 'b.region_id')
                ->orderBy('t.' . $field, $sortOrder);
        } elseif ($sortKey === 'status.name') {
            $relationTable = 'member_statuses';
            $field = 'name';

            return $query->select("{$table}.*")
                ->join($relationTable . ' as t', 't.code', '=', "{$table}.member_status_code")
                ->orderBy('t.' . $field, $sortOrder);
        }

        return $query->orderBy($sortKey, $sortOrder);
    }

    /**
     * Custom query for searching.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $kKeyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSearch(Builder $query, $keyword)
    {
        return $query->where(function (Builder $q) use ($keyword) {
            foreach ($this->searchable as $field) {
                if (in_array($field, ['branch.region', 'branch'])) {
                    $q->orWhereHas(
                        $field,
                        fn (Builder $q) => $q->where('name', 'like', $keyword)
                    );
                } else {
                    $q->orWhere($field, 'like', $keyword);
                }
            }
        });
    }

    /**
     * Runs query with filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithFilter(Builder $query, $filters = [])
    {
        if (isset($filters['region_id']) && $filters['region_id']) {
            $query = $query->whereHas('branch.region', fn ($q) => $q->where('id', $filters['region_id']));
        }

        if (isset($filters['branch_id']) && $filters['branch_id']) {
            $query = $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['retirement_age_id']) && $filters['retirement_age_id']) {
            $query = $query->where('retirement_age_id', $filters['retirement_age_id']);
        }

        return $query;
    }

    /**
     * Query members that will retire this year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWillRetireThisYear(Builder $query)
    {
        return $query->select('members.*')
            ->addSelect(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as year_age'))
            ->addSelect(DB::raw('DATE_ADD(birth_date, INTERVAL ra.age YEAR) as retirement_date'))
            ->addSelect('ra.age')
            ->join('retirement_ages as ra', 'ra.id', '=', 'retirement_age_id')
            ->having(DB::raw('YEAR((retirement_date))'), date('Y'))
            ->havingBetween('year_age', [DB::raw('ra.age - 1'), DB::raw('ra.age')])
            ->orderBy('birth_date');
    }

    /**
     * Scope query for filters by user role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByRole(Builder $query, User $user)
    {
        if ($user->is_member) {
            if ($user->hasRole('branch')) {
                $user->load('member.branch');

                $query = $query->where('branch_id', $user->member->branch_id);
            } elseif ($user->hasRole('region')) {
                $user->load('member.branch.region');

                $query = $query->whereHas(
                    'branch.region',
                    fn ($q) => $q->where('id', $user->member->branch->region_id)
                );
            }
        }

        return $query;
    }
}
