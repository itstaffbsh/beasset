$u = App\Models\User::first(); auth()->login($u); echo json_encode(App\Models\User::with(["office", "department"])->where("id", "!=", auth()->id())->orderBy("id", "desc")->paginate(10)->toArray());
